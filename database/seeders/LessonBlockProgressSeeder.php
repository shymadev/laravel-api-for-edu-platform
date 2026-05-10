<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Populates user_lesson_block_progress for every lesson already marked as
 * completed in user_completed_lessons.
 *
 * For each completed lesson all content blocks are inserted as is_completed=true
 * so that derived statistics (completed exercises, listened audio) are consistent
 * with the lesson/course progress seeded by UserProgressSeeder.
 *
 * Block types drive two key counters in CourseProgressService::userStatistics():
 *   – totalCompletedExercises  → block_type != 'audio', is_completed = true
 *   – totalListenedAudio       → block_type  = 'audio', is_completed = true
 *
 * block_index is the 0-based position of the block inside lessons.content (the
 * same value the frontend sends when it calls saveBlockProgress).
 */
class LessonBlockProgressSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $this->command->info('Seeding lesson block progress...');

        // Load all (user_id, lesson_id, content) rows in a single joined query.
        // Chunk to keep memory bounded.
        $totalCompleted = DB::table('user_completed_lessons')->count();

        if ($totalCompleted === 0) {
            $this->command->warn('No completed lessons found — skipping LessonBlockProgressSeeder.');

            return;
        }

        // Pre-load existing (user_id, lesson_id, block_index) combos to skip duplicates.
        $this->command->info('Loading existing block progress records...');
        $existingSet = DB::table('user_lesson_block_progress')
            ->select('user_id', 'lesson_id', 'block_index')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->user_id}:{$r->lesson_id}:{$r->block_index}" => true]);

        $bar = $this->command->getOutput()->createProgressBar($totalCompleted);
        $bar->start();

        $inserted = 0;
        $skipped = 0;
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
                &$skipped,
                &$existingSet,
                $now,
                $bar,
            ): void {
                foreach ($rows as $row) {
                    $content = $row->content;

                    if (is_string($content)) {
                        $content = json_decode($content, true);
                    }

                    if (empty($content) || !is_array($content)) {
                        $bar->advance();

                        continue;
                    }

                    foreach ($content as $index => $block) {
                        $key = "{$row->user_id}:{$row->lesson_id}:{$index}";

                        if (isset($existingSet[$key])) {
                            $skipped++;

                            continue;
                        }

                        $existingSet[$key] = true;

                        $batch[] = [
                            'user_id' => $row->user_id,
                            'lesson_id' => $row->lesson_id,
                            'block_index' => $index,
                            'block_type' => $block['type'] ?? 'unknown',
                            'is_completed' => true,
                            'block_state' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $inserted++;

                        if (count($batch) >= self::BATCH_SIZE) {
                            DB::table('user_lesson_block_progress')->insertOrIgnore($batch);
                            $batch = [];
                        }
                    }

                    $bar->advance();
                }
            });

        if (!empty($batch)) {
            DB::table('user_lesson_block_progress')->insertOrIgnore($batch);
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Block progress seeded: {$inserted} inserted, {$skipped} already existed.");
    }
}
