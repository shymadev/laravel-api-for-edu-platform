<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\CourseReview;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Seeds course reviews for users who have completed at least one lesson.
 *
 * Data is driven by database/seeders/data/users/reviews.json.
 * Each entry: { user_index, course_title, rating, review_text }.
 *
 * A review is only written when the user actually has at least one
 * completed lesson in that course (enforced at seed time).
 */
class CourseReviewSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding course reviews...');

        $usersData = $this->loadJson('users/users.json');
        $reviewsData = $this->loadJson('users/reviews.json');

        if ($usersData === [] || $reviewsData === []) {
            $this->command->warn('Required JSON files missing — skipping CourseReviewSeeder.');

            return;
        }

        $userMap = $this->buildUserMap($usersData);
        $courseMap = Course::pluck('id', 'title');

        $created = 0;
        $skipped = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($reviewsData));
        $bar->start();

        foreach ($reviewsData as $entry) {
            $userIndex = $entry['user_index'];
            $courseTitle = $entry['course_title'];

            if (!isset($usersData[$userIndex])) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $email = $usersData[$userIndex]['email'];
            $user = $userMap->get($email);
            $courseId = $courseMap->get($courseTitle);

            if ($user === null || $courseId === null) {
                $skipped++;
                $bar->advance();

                continue;
            }

            if (!$this->hasCompletedAnyLesson($user->id, $courseId)) {
                $skipped++;
                $bar->advance();

                continue;
            }

            if (CourseReview::query()->where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
                $skipped++;
                $bar->advance();

                continue;
            }

            try {
                CourseReview::create([
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                    'rating' => $entry['rating'],
                    'review_text' => $entry['review_text'] ?? null,
                ]);
                $created++;
            } catch (\Throwable $e) {
                Log::error('CourseReviewSeeder: error', [
                    'user' => $email,
                    'course' => $courseTitle,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Reviews seeded: {$created} created, {$skipped} skipped.");
    }

    /**
     * Check whether a user has completed at least one lesson that belongs to
     * the given course (via topic → course relationship).
     *
     * @param int $userId
     * @param int $courseId
     */
    private function hasCompletedAnyLesson(int $userId, int $courseId): bool
    {
        return UserCompletedLesson::query()
            ->where('user_id', $userId)
            ->whereHas('lesson.topic', fn ($q) => $q->where('course_id', $courseId))
            ->exists();
    }

    /**
     * @param array<int, array<string, mixed>> $usersData
     *
     * @return Collection<string, User>
     */
    private function buildUserMap(array $usersData): Collection
    {
        $emails = collect($usersData)->pluck('email');

        return User::whereIn('email', $emails)->get()->keyBy('email');
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
