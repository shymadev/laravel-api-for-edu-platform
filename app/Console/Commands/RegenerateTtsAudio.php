<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Education\Lesson;
use App\Models\Education\Phrase;
use App\Services\Lesson\Enums\ParagraphType;
use App\Services\Lesson\Enums\VocabularyGameType;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to regenerate TTS audio for phrases, lesson audio blocks, and vocabulary game items.
 */
class RegenerateTtsAudio extends Command
{
    private const TYPE_PHRASES = 'phrases';

    private const TYPE_LESSON_AUDIO = 'lesson-audio';

    private const TYPE_LESSON_GAMES = 'lesson-games';

    private const ALL_TYPES = [self::TYPE_PHRASES, self::TYPE_LESSON_AUDIO, self::TYPE_LESSON_GAMES];

    protected $signature = 'tts:regenerate
                            {--type=* : Types to process: phrases, lesson-audio, lesson-games (omit for all)}
                            {--force : Regenerate even when audio already exists}
                            {--dry-run : Preview what would be regenerated without making changes}
                            {--retries=3 : Generation/upload attempts per item}';

    protected $description = 'Regenerate TTS audio for phrases, lesson audio paragraphs, and vocabulary game items';

    /**
     * @param TTSService $ttsService
     * @param AudioStorageService $audioStorage
     */
    public function __construct(
        private readonly TTSService $ttsService,
        private readonly AudioStorageService $audioStorage,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return integer Exit code ({@see Command::SUCCESS} or {@see Command::FAILURE})
     */
    public function handle(): int
    {
        if (!$this->ttsService->isHealthy()) {
            $this->error('TTS service is not reachable. Check TTS_SERVICE_URL and that the service is running.');

            return self::FAILURE;
        }

        $types = $this->resolveTypes();
        $isDryRun = $this->option('dry-run') === true;
        $force = $this->option('force') === true;
        $maxAttempts = $this->resolveMaxAttempts();

        if ($isDryRun) {
            $this->info('[dry-run] No changes will be written.');
        }

        $this->info('Processing types: ' . implode(', ', $types));
        $this->newLine();

        foreach ($types as $type) {
            match ($type) {
                self::TYPE_PHRASES => $this->processPhrases($force, $isDryRun, $maxAttempts),
                self::TYPE_LESSON_AUDIO => $this->processLessonAudio($force, $isDryRun, $maxAttempts),
                self::TYPE_LESSON_GAMES => $this->processLessonGames($force, $isDryRun, $maxAttempts),
                default => null,
            };
        }

        return self::SUCCESS;
    }

    /**
     * @return integer
     */
    private function resolveMaxAttempts(): int
    {
        $attempts = (int) $this->option('retries');

        return max(1, min($attempts, 10));
    }

    /**
     * @return string[]
     */
    private function resolveTypes(): array
    {
        /** @var string[] $requested */
        $requested = $this->option('type');

        if ($requested === []) {
            return self::ALL_TYPES;
        }

        $invalid = array_diff($requested, self::ALL_TYPES);
        if ($invalid !== []) {
            $this->warn('Unknown types ignored: ' . implode(', ', $invalid));
        }

        $valid = array_intersect($requested, self::ALL_TYPES);

        return array_values($valid);
    }

    /**
     * Regenerate TTS files for phrase records.
     *
     * @param bool $force
     * @param bool $isDryRun
     * @param int $maxAttempts
     *
     * @return void
     */
    private function processPhrases(bool $force, bool $isDryRun, int $maxAttempts): void
    {
        $this->info('--- Phrases ---');

        $total = Phrase::query()
            ->when(!$force, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('audio')->orWhere('audio', '')))
            ->count();

        $this->line("Found {$total} phrase(s) to process.");

        if ($total === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $regenerated = 0;
        $failed = 0;
        $failures = [];

        Phrase::query()
            ->when(!$force, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('audio')->orWhere('audio', '')))
            ->each(function (Phrase $phrase) use ($force, $isDryRun, $maxAttempts, $bar, &$regenerated, &$failed, &$failures): void {
                $bar->advance();

                try {
                    if ($isDryRun) {
                        $regenerated++;

                        return;
                    }

                    $oldAudio = $phrase->audio;
                    $newPath = $this->generateAndUploadAudio($phrase->text, 'phrases', [
                        'type' => self::TYPE_PHRASES,
                        'phrase_id' => $phrase->id,
                    ], $maxAttempts);

                    if ($newPath === false) {
                        $failed++;
                        $failures[] = "phrase #{$phrase->id}: {$phrase->text}";

                        return;
                    }

                    $phrase->update(['audio' => $newPath]);

                    if (
                        $force
                        && $oldAudio !== null
                        && $oldAudio !== ''
                        && $oldAudio !== $newPath
                        && !$this->isPhraseAudioReferenced($oldAudio)
                    ) {
                        $this->audioStorage->delete($oldAudio);
                    }

                    $regenerated++;
                } catch (\Throwable $e) {
                    $failed++;
                    $failures[] = "phrase #{$phrase->id}: {$phrase->text}";
                    Log::error('tts:regenerate phrases error', [
                        'phrase_id' => $phrase->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        $bar->finish();
        $this->newLine();
        $this->printSummary($regenerated, $failed, $isDryRun, $failures);
    }

    /**
     * Regenerate TTS for lesson paragraphs of type audio.
     *
     * @param bool $force
     * @param bool $isDryRun
     * @param int $maxAttempts
     *
     * @return void
     */
    private function processLessonAudio(bool $force, bool $isDryRun, int $maxAttempts): void
    {
        $this->info('--- Lesson audio paragraphs ---');

        $lessons = Lesson::query()
            ->whereNotNull('content')
            ->whereRaw("JSON_CONTAINS(
                JSON_EXTRACT(content, '$[*].type'),
                JSON_QUOTE(?),
                '$'
            )", [ParagraphType::AUDIO->value])
            ->get();

        $this->line("Found {$lessons->count()} lesson(s) with audio paragraphs.");

        $regenerated = 0;
        $failed = 0;
        $failures = [];

        $bar = $this->output->createProgressBar($lessons->count());
        $bar->start();

        foreach ($lessons as $lesson) {
            $bar->advance();
            $content = $lesson->content ?? [];
            $dirty = false;

            foreach ($content as $i => $paragraph) {
                if ($paragraph['type'] !== ParagraphType::AUDIO->value) {
                    continue;
                }

                $block = $paragraph['content'] ?? [];

                if (($block['type'] ?? '') !== 'tts' || !isset($block['text'])) {
                    continue;
                }

                $hasAudio = isset($block['audio_url']) && $block['audio_url'] !== '';

                if ($hasAudio && !$force) {
                    continue;
                }

                try {
                    if ($isDryRun) {
                        $regenerated++;

                        continue;
                    }

                    $oldAudio = $block['audio_url'] ?? null;
                    $newPath = $this->generateAndUploadAudio($block['text'], 'lessons/audio_paragraphs', [
                        'type' => self::TYPE_LESSON_AUDIO,
                        'lesson_id' => $lesson->id,
                        'paragraph_index' => $i,
                    ], $maxAttempts);

                    if ($newPath === false) {
                        $failed++;
                        $failures[] = "lesson #{$lesson->id}, paragraph {$i}";

                        continue;
                    }

                    $content[$i]['content']['audio_url'] = $newPath;

                    if ($force && $hasAudio && $oldAudio !== $newPath) {
                        $this->audioStorage->delete($oldAudio);
                    }

                    $dirty = true;
                    $regenerated++;
                } catch (\Throwable $e) {
                    $failed++;
                    $failures[] = "lesson #{$lesson->id}, paragraph {$i}";
                    Log::error('tts:regenerate lesson-audio error', [
                        'lesson_id' => $lesson->id,
                        'paragraph_index' => $i,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($dirty) {
                $lesson->update(['content' => $content]);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->printSummary($regenerated, $failed, $isDryRun, $failures);
    }

    /**
     * Regenerate TTS for listen-and-write vocabulary game items.
     *
     * @param bool $force
     * @param bool $isDryRun
     * @param int $maxAttempts
     *
     * @return void
     */
    private function processLessonGames(bool $force, bool $isDryRun, int $maxAttempts): void
    {
        $this->info('--- Lesson vocabulary game items ---');

        $lessons = Lesson::query()
            ->whereNotNull('content')
            ->whereRaw("JSON_CONTAINS(
                JSON_EXTRACT(content, '$[*].type'),
                JSON_QUOTE(?),
                '$'
            )", [ParagraphType::VOCABULARY_GAME->value])
            ->get();

        $this->line("Found {$lessons->count()} lesson(s) with vocabulary games.");

        $regenerated = 0;
        $failed = 0;
        $failures = [];

        $bar = $this->output->createProgressBar($lessons->count());
        $bar->start();

        foreach ($lessons as $lesson) {
            $bar->advance();
            $content = $lesson->content ?? [];
            $dirty = false;

            foreach ($content as $i => $paragraph) {
                if ($paragraph['type'] !== ParagraphType::VOCABULARY_GAME->value) {
                    continue;
                }

                if (($paragraph['gameType'] ?? '') !== VocabularyGameType::LISTEN_WRITE->value) {
                    continue;
                }

                $listenItems = $paragraph['listenItems'] ?? [];

                foreach ($listenItems as $j => $item) {
                    if (($item['type'] ?? 'tts') !== 'tts' || !isset($item['text'])) {
                        continue;
                    }

                    $hasAudio = isset($item['audio_url']) && $item['audio_url'] !== '';

                    if ($hasAudio && !$force) {
                        continue;
                    }

                    try {
                        if ($isDryRun) {
                            $regenerated++;

                            continue;
                        }

                        $oldAudio = $item['audio_url'] ?? null;
                        $newPath = $this->generateAndUploadAudio($item['text'], 'lessons/vocabulary_games', [
                            'type' => self::TYPE_LESSON_GAMES,
                            'lesson_id' => $lesson->id,
                            'paragraph_index' => $i,
                            'item_index' => $j,
                        ], $maxAttempts);

                        if ($newPath === false) {
                            $failed++;
                            $failures[] = "lesson #{$lesson->id}, paragraph {$i}, item {$j}";

                            continue;
                        }

                        $content[$i]['listenItems'][$j]['audio_url'] = $newPath;

                        if ($force && $hasAudio && $oldAudio !== $newPath) {
                            $this->audioStorage->delete($oldAudio);
                        }

                        $dirty = true;
                        $regenerated++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $failures[] = "lesson #{$lesson->id}, paragraph {$i}, item {$j}";
                        Log::error('tts:regenerate lesson-games error', [
                            'lesson_id' => $lesson->id,
                            'paragraph_index' => $i,
                            'item_index' => $j,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            if ($dirty) {
                $lesson->update(['content' => $content]);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->printSummary($regenerated, $failed, $isDryRun, $failures);
    }

    /**
     * @param string $text
     * @param string $folderName
     * @param array<string, mixed> $context
     * @param int $maxAttempts
     *
     * @return string|false
     */
    private function generateAndUploadAudio(string $text, string $folderName, array $context, int $maxAttempts): string|false
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $audio = $this->ttsService->generateAudio($text);
                $path = $this->audioStorage->upload($audio, $folderName);

                if ($path !== false && $this->audioStorage->exists($path)) {
                    return $path;
                }

                Log::warning('tts:regenerate upload failed', $context + [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'path' => $path === false ? null : $path,
                ]);
            } catch (\Throwable $e) {
                Log::warning('tts:regenerate attempt failed', $context + [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('tts:regenerate failed after retries', $context + [
            'max_attempts' => $maxAttempts,
        ]);

        return false;
    }

    /**
     * @param string $audio
     *
     * @return boolean
     */
    private function isPhraseAudioReferenced(string $audio): bool
    {
        return Phrase::query()
            ->where('audio', $audio)
            ->exists();
    }

    /**
     * Print a one-line summary for the last processed batch.
     *
     * @param int $regenerated
     * @param int $failed
     * @param bool $isDryRun
     * @param string[] $failures
     *
     * @return void
     */
    private function printSummary(int $regenerated, int $failed, bool $isDryRun, array $failures = []): void
    {
        $prefix = $isDryRun ? '[dry-run] ' : '';
        $this->info("{$prefix}Regenerated: {$regenerated}, Failed: {$failed}");

        if ($failures !== []) {
            $this->warn('Failed items:');

            foreach (array_slice($failures, 0, 20) as $failure) {
                $this->line("  - {$failure}");
            }

            $remaining = count($failures) - 20;
            if ($remaining > 0) {
                $this->line("  ...and {$remaining} more");
            }
        }

        $this->newLine();
    }
}
