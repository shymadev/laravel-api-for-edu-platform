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
                            {--dry-run : Preview what would be regenerated without making changes}';

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

        if ($isDryRun) {
            $this->info('[dry-run] No changes will be written.');
        }

        $this->info('Processing types: ' . implode(', ', $types));
        $this->newLine();

        foreach ($types as $type) {
            match ($type) {
                self::TYPE_PHRASES => $this->processPhrases($force, $isDryRun),
                self::TYPE_LESSON_AUDIO => $this->processLessonAudio($force, $isDryRun),
                self::TYPE_LESSON_GAMES => $this->processLessonGames($force, $isDryRun),
                default => null,
            };
        }

        return self::SUCCESS;
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
     *
     * @return void
     */
    private function processPhrases(bool $force, bool $isDryRun): void
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

        Phrase::query()
            ->when(!$force, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('audio')->orWhere('audio', '')))
            ->each(function (Phrase $phrase) use ($force, $isDryRun, $bar, &$regenerated, &$failed): void {
                $bar->advance();

                try {
                    if ($isDryRun) {
                        $regenerated++;

                        return;
                    }

                    $newAudio = $this->ttsService->generateAudio($phrase->text);
                    $newPath = $this->audioStorage->upload($newAudio, 'phrases');

                    if ($newPath === false) {
                        $failed++;

                        return;
                    }

                    if ($force && $phrase->audio !== null && $phrase->audio !== '') {
                        $this->audioStorage->delete($phrase->audio);
                    }

                    $phrase->update(['audio' => $newPath]);
                    $regenerated++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('tts:regenerate phrases error', [
                        'phrase_id' => $phrase->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        $bar->finish();
        $this->newLine();
        $this->printSummary($regenerated, $failed, $isDryRun);
    }

    /**
     * Regenerate TTS for lesson paragraphs of type audio.
     *
     * @param bool $force
     * @param bool $isDryRun
     *
     * @return void
     */
    private function processLessonAudio(bool $force, bool $isDryRun): void
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

                    $newAudio = $this->ttsService->generateAudio($block['text']);
                    $newPath = $this->audioStorage->upload($newAudio, 'lessons/audio_paragraphs');

                    if ($newPath === false) {
                        $failed++;

                        continue;
                    }

                    if ($force && $hasAudio) {
                        $this->audioStorage->delete($block['audio_url']);
                    }

                    $content[$i]['content']['audio_url'] = $newPath;
                    $dirty = true;
                    $regenerated++;
                } catch (\Throwable $e) {
                    $failed++;
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
        $this->printSummary($regenerated, $failed, $isDryRun);
    }

    /**
     * Regenerate TTS for listen-and-write vocabulary game items.
     *
     * @param bool $force
     * @param bool $isDryRun
     *
     * @return void
     */
    private function processLessonGames(bool $force, bool $isDryRun): void
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

                        $newAudio = $this->ttsService->generateAudio($item['text']);
                        $newPath = $this->audioStorage->upload($newAudio, 'lessons/vocabulary_games');

                        if ($newPath === false) {
                            $failed++;

                            continue;
                        }

                        if ($force && $hasAudio) {
                            $this->audioStorage->delete($item['audio_url']);
                        }

                        $content[$i]['listenItems'][$j]['audio_url'] = $newPath;
                        $dirty = true;
                        $regenerated++;
                    } catch (\Throwable $e) {
                        $failed++;
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
        $this->printSummary($regenerated, $failed, $isDryRun);
    }

    /**
     * Print a one-line summary for the last processed batch.
     *
     * @param int $regenerated
     * @param int $failed
     * @param bool $isDryRun
     *
     * @return void
     */
    private function printSummary(int $regenerated, int $failed, bool $isDryRun): void
    {
        $prefix = $isDryRun ? '[dry-run] ' : '';
        $this->info("{$prefix}Regenerated: {$regenerated}, Failed: {$failed}");
        $this->newLine();
    }
}
