<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Seeds lesson block progress.
 */
class LessonBlockProgressSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    /**
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding lesson block progress...');

        $totalCompleted = DB::table('user_completed_lessons')->count();

        if ($totalCompleted === 0) {
            $this->command->warn('No completed lessons found — skipping LessonBlockProgressSeeder.');

            return;
        }

        $this->command->info('Loading existing block progress records...');
        $existingSet = DB::table('user_lesson_block_progress')
            ->select('user_id', 'lesson_id', 'block_index')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->user_id}:{$r->lesson_id}:{$r->block_index}" => true]);

        $bar = $this->command->getOutput()->createProgressBar($totalCompleted);
        $bar->start();

        $inserted = 0;
        $updated = 0;
        $batch = [];
        $now = now()->toDateTimeString();

        DB::table('user_completed_lessons')
            ->join('lessons', 'lessons.id', '=', 'user_completed_lessons.lesson_id')
            ->select(
                'user_completed_lessons.user_id',
                'lessons.id as lesson_id',
                'lessons.content',
            )
            ->orderBy('user_completed_lessons.user_id')
            ->orderBy('user_completed_lessons.lesson_id')
            ->chunk(200, function ($rows) use (
                &$batch,
                &$inserted,
                &$updated,
                &$existingSet,
                $now,
                $bar,
            ): void {
                foreach ($rows as $row) {
                    $content = $row->content;

                    if (is_string($content)) {
                        $content = json_decode($content, true);
                    }

                    if (!is_array($content) || $content === []) {
                        $bar->advance();

                        continue;
                    }

                    foreach ($content as $index => $block) {
                        $key = "{$row->user_id}:{$row->lesson_id}:{$index}";
                        $blockState = $this->buildCompletedBlockState($block);

                        if (isset($existingSet[$key])) {
                            $updated++;
                        } else {
                            $existingSet[$key] = true;
                            $inserted++;
                        }

                        $batch[] = [
                            'user_id' => $row->user_id,
                            'lesson_id' => $row->lesson_id,
                            'block_index' => $index,
                            'block_type' => $block['type'] ?? 'unknown',
                            'is_completed' => true,
                            'block_state' => $blockState !== null
                                ? json_encode($blockState, JSON_UNESCAPED_UNICODE)
                                : null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($batch) >= self::BATCH_SIZE) {
                            $this->upsertBatch($batch);
                            $batch = [];
                        }
                    }

                    $bar->advance();
                }
            });

        if ($batch !== []) {
            $this->upsertBatch($batch);
        }

        DB::table('user_lesson_block_progress')
            ->where('is_completed', true)
            ->whereNull('block_state')
            ->update([
                'block_state' => json_encode(['completed' => true], JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

        Cache::tags(['progress'])->flush();

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info(
            "Block progress seeded: {$inserted} inserted, {$updated} state updates.",
        );
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     *
     * @return void
     */
    private function upsertBatch(array $batch): void
    {
        DB::table('user_lesson_block_progress')->upsert(
            $batch,
            ['user_id', 'lesson_id', 'block_index'],
            ['block_type', 'is_completed', 'block_state', 'updated_at'],
        );
    }

    /**
     * @param mixed $block
     *
     * @return array<string, mixed>|null
     */
    private function buildCompletedBlockState(mixed $block): ?array
    {
        if (!is_array($block)) {
            return null;
        }

        return match ($block['type'] ?? null) {
            'audio' => ['listened' => true],
            'test' => $this->buildTestState($block),
            'matching' => $this->buildMatchingState($block),
            'fill-gaps' => $this->buildFillGapsState($block),
            'categorization' => $this->buildCategorizationState($block),
            'translation' => $this->buildTranslationState($block),
            'vocabulary-game' => $this->buildVocabularyGameState($block),
            'speech-recognition' => ['completed' => true],
            default => ['completed' => true],
        };
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildTestState(array $block): array
    {
        $answers = [];

        foreach ($block['questions'] ?? [] as $questionIndex => $question) {
            if (!is_array($question)) {
                continue;
            }

            $questionId = $question['id'] ?? $questionIndex;
            $correctOptionId = '';

            if (isset($question['correctOptions']) && is_array($question['correctOptions'])) {
                $correctOptionId = $question['correctOptions'][0] ?? '';
            } else {
                foreach ($question['options'] ?? [] as $optionIndex => $option) {
                    if (!is_array($option)) {
                        continue;
                    }

                    if (($option['is_correct'] ?? false) === true) {
                        $correctOptionId = $option['id'] ?? $optionIndex;
                        break;
                    }
                }
            }

            $answers[$questionId] = $correctOptionId;
        }

        return [
            'answers' => $answers,
            'submitted' => true,
            'allCorrect' => true,
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildMatchingState(array $block): array
    {
        return [
            'matchedIds' => collect($block['pairs'] ?? [])
                ->filter(fn ($pair): bool => is_array($pair) && isset($pair['id']))
                ->pluck('id')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildFillGapsState(array $block): array
    {
        $userInputs = [];
        $results = [];

        foreach ($block['gaps'] ?? [] as $index => $gap) {
            if (!is_array($gap)) {
                continue;
            }

            $userInputs[$index] = $gap['correctAnswers'][0] ?? $gap['answers'][0] ?? $gap['answer'] ?? '';
            $results[$index] = true;
        }

        return [
            'userInputs' => $userInputs,
            'results' => $results,
            'isChecked' => true,
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildCategorizationState(array $block): array
    {
        $placedItems = [];
        $results = [];

        foreach ($block['items'] ?? [] as $item) {
            if (!is_array($item) || !isset($item['id'])) {
                continue;
            }

            $placedItems[$item['id']] = $item['correctCategory'] ?? '';
            $results[$item['id']] = true;
        }

        return [
            'placedItems' => $placedItems,
            'results' => $results,
            'isChecked' => true,
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildTranslationState(array $block): array
    {
        $answers = [];
        $results = [];
        $showAnswers = [];

        foreach ($block['pairs'] ?? [] as $pairIndex => $pair) {
            if (!is_array($pair)) {
                continue;
            }

            $pairId = $pair['id'] ?? $pairIndex;
            $answers[$pairId] = ($pair['direction'] ?? null) === 'ru-eng'
                ? ($pair['english'] ?? '')
                : ($pair['russian'] ?? '');
            $results[$pairId] = true;
            $showAnswers[$pairId] = false;
        }

        return [
            'answers' => $answers,
            'results' => $results,
            'showAnswers' => $showAnswers,
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private function buildVocabularyGameState(array $block): array
    {
        return match ($block['gameType'] ?? null) {
            'word-scramble' => $this->buildIndexedAnswerState($block['words'] ?? []),
            'guess-word' => $this->buildIndexedAnswerState($block['guessItems'] ?? [], 'word'),
            'odd-one-out' => $this->buildOddOneOutState($block['oddItems'] ?? []),
            'find-mistake' => $this->buildIndexedAnswerState($block['mistakeItems'] ?? [], 'correctText'),
            'listen-write' => $this->buildIndexedAnswerState($block['listenItems'] ?? [], 'correctText'),
            default => ['completed' => true],
        };
    }

    /**
     * @param array<int, mixed> $items
     * @param string|null $answerKey
     *
     * @return array<string, mixed>
     */
    private function buildIndexedAnswerState(array $items, ?string $answerKey = null): array
    {
        $answers = [];
        $feedback = [];

        foreach ($items as $index => $item) {
            $answers[$index] = $answerKey !== null && is_array($item)
                ? ($item[$answerKey] ?? '')
                : $item;
            $feedback[$index] = 'correct';
        }

        return [
            'answers' => $answers,
            'feedback' => $feedback,
        ];
    }

    /**
     * @param array<int, mixed> $items
     *
     * @return array<string, mixed>
     */
    private function buildOddOneOutState(array $items): array
    {
        $selected = [];
        $feedback = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $selected[$index] = $item['correctWord'] ?? '';
            $feedback[$index] = 'correct';
        }

        return [
            'selected' => $selected,
            'feedback' => $feedback,
        ];
    }
}
