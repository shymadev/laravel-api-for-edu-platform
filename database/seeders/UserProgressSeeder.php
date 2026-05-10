<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use App\Models\User\UserCourseStatistics;
use App\Models\User\UserStoppedCourse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Seeds realistic user progress across courses.
 *
 * Data is driven by database/seeders/data/users/progress.json.
 * Each entry has a user_index (index into users.json) and a list of
 * {course_title, scenario} pairs.
 *
 * Scenarios:
 *   completed   – all lessons marked complete
 *   half        – ~50 % of lessons complete
 *   quarter     – ~25 % of lessons complete
 *   just_started – exactly 1 lesson complete
 *   stopped     – enrolled + ~30 % lessons + course added to stopped list
 */
class UserProgressSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding user progress...');

        $usersData = $this->loadJson('users/users.json');
        $progressData = $this->loadJson('users/progress.json');

        if ($usersData === [] || $progressData === []) {
            $this->command->warn('Required JSON files missing — skipping UserProgressSeeder.');

            return;
        }

        $userMap = $this->buildUserMap($usersData);

        $courseLessonsMap = $this->buildCourseLessonsMap();

        $enrollCount = 0;
        $completedCount = 0;
        $stoppedCount = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($progressData));
        $bar->start();

        foreach ($progressData as $entry) {
            $userIndex = $entry['user_index'];

            if (!isset($usersData[$userIndex])) {
                $bar->advance();

                continue;
            }

            $email = $usersData[$userIndex]['email'];
            $user = $userMap->get($email);

            if ($user === null) {
                $bar->advance();

                continue;
            }

            foreach ($entry['courses'] as $courseEntry) {
                $courseTitle = $courseEntry['course_title'];
                $scenario = $courseEntry['scenario'];

                $lessonIds = $courseLessonsMap->get($courseTitle);

                if ($lessonIds === null || $lessonIds->isEmpty()) {
                    continue;
                }

                $course = Course::where('title', $courseTitle)->first();

                if ($course === null) {
                    continue;
                }

                try {
                    UserCourseStatistics::firstOrCreate([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                    ]);
                    $enrollCount++;

                    $total = $lessonIds->count();
                    $lessonsToMark = $this->resolveLessonCount($scenario, $total);

                    $toComplete = $lessonIds->take($lessonsToMark);

                    foreach ($toComplete as $lessonId) {
                        UserCompletedLesson::firstOrCreate([
                            'user_id' => $user->id,
                            'lesson_id' => $lessonId,
                        ]);
                        $completedCount++;
                    }

                    if ($scenario === 'stopped') {
                        UserStoppedCourse::firstOrCreate(
                            ['user_id' => $user->id, 'course_id' => $course->id],
                            ['stopped_at' => now()->subDays(rand(1, 60))],
                        );
                        $stoppedCount++;
                    }
                } catch (\Throwable $e) {
                    Log::error('UserProgressSeeder: error', [
                        'user' => $email,
                        'course' => $courseTitle,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info(
            "Progress seeded: {$enrollCount} enrollments, "
            . "{$completedCount} completed lessons, "
            . "{$stoppedCount} stopped courses.",
        );
    }

    /**
     * Resolve how many lessons to mark complete for a given scenario and total.
     *
     * Scenarios:
     *   completed   → 100 %
     *   half        → ~50 %
     *   quarter     → ~25 %
     *   fifth       → ~20 %
     *   two_three   → exactly 2 lessons (or 3 if course has ≥ 6 lessons)
     *   just_started → exactly 1 lesson
     *   stopped     → ~30 %
     *
     * @param string $scenario
     * @param int $total
     */
    private function resolveLessonCount(string $scenario, int $total): int
    {
        return match ($scenario) {
            'completed' => $total,
            'half' => max(1, (int) round($total * 0.5)),
            'quarter' => max(1, (int) round($total * 0.25)),
            'fifth' => max(1, (int) round($total * 0.20)),
            'two_three' => $total >= 6 ? 3 : 2,
            'just_started' => 1,
            'stopped' => max(1, (int) round($total * 0.30)),
            default => 0,
        };
    }

    /**
     * Build a map of course title → ordered Collection of lesson IDs.
     * Lessons are ordered by topic weight (parent first), then lesson weight.
     */
    private function buildCourseLessonsMap(): Collection
    {
        $map = collect();

        Course::with([
            'topics' => function ($q): void {
                $q->whereNull('parent_id')->orderBy('id');
            },
            'topics.subtopics' => function ($q): void {
                $q->orderBy('id');
            },
            'topics.lessons' => function ($q): void {
                $q->orderBy('weight')->orderBy('id');
            },
            'topics.subtopics.lessons' => function ($q): void {
                $q->orderBy('weight')->orderBy('id');
            },
        ])->each(function (Course $course) use ($map): void {
            $lessonIds = collect();

            foreach ($course->topics as $topic) {
                foreach ($topic->lessons->sortBy(['weight', 'id']) as $lesson) {
                    $lessonIds->push($lesson->id);
                }
                foreach ($topic->subtopics->sortBy('id') as $sub) {
                    foreach ($sub->lessons->sortBy(['weight', 'id']) as $lesson) {
                        $lessonIds->push($lesson->id);
                    }
                }
            }

            $map->put($course->title, $lessonIds->unique());
        });

        return $map;
    }

    /**
     * Build email → User Eloquent model map for all users in the JSON list.
     *
     * @param array<int, array<string, mixed>> $usersData
     *
     * @return Collection<string, User>
     */
    private function buildUserMap(array $usersData): Collection
    {
        $emails = collect($usersData)->pluck('email');

        return User::whereIn('email', $emails)
            ->get()
            ->keyBy('email');
    }

    /**
     * @param string $relativePath
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadJson(string $relativePath): array
    {
        $path = database_path('seeders/data/' . $relativePath);

        if (!File::exists($path)) {
            $this->command->warn("JSON file not found: {$path}");

            return [];
        }

        return json_decode(File::get($path), true) ?? [];
    }
}
