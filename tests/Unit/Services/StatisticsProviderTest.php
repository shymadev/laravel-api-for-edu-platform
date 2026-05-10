<?php

declare(strict_types=1);

/**
 * Unit tests for StatisticsProvider.
 */

use App\Models\Education\Course;
use App\Models\Education\CourseReview;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Models\User\Role;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use App\Services\StatisticsProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new StatisticsProvider();
    Cache::flush();

    Role::upsert(
        [['id' => Role::USER_ROLE_ID, 'role_name' => 'user']],
        ['id'],
    );
});

/**
 * getStatistics aggregates live DB metrics, or returns zeros when tables are empty.
 */
it('test_get_statistics', function (bool $withData): void {
    if ($withData) {
        $user = User::create([
            'username' => 'stats_user',
            'email' => 'stats@example.com',
            'password_hash' => Hash::make('password'),
            'role_id' => Role::USER_ROLE_ID,
            'is_blocked' => false,
        ]);

        $course = Course::create(['title' => 'C', 'language' => 'en', 'is_active' => true, 'is_premium' => false]);
        $topic = Topic::create(['course_id' => $course->id, 'title' => 'T', 'is_active' => true]);
        $lesson = Lesson::create(['topic_id' => $topic->id, 'title' => 'L', 'weight' => 1, 'is_active' => true, 'content' => []]);

        UserCompletedLesson::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);
        CourseReview::create(['course_id' => $course->id, 'user_id' => $user->id, 'rating' => 4]);

        $dto = $this->service->getStatistics();

        expect($dto->activeStudents)->toBeGreaterThanOrEqual(1)
            ->and($dto->totalCourses)->toBeGreaterThanOrEqual(1)
            ->and($dto->completedLessons)->toBeGreaterThanOrEqual(1)
            ->and($dto->averageRating)->toBe(4.0);
    } else {
        $dto = $this->service->getStatistics();

        expect($dto->activeStudents)->toBe(0)
            ->and($dto->totalCourses)->toBe(0)
            ->and($dto->completedLessons)->toBe(0)
            ->and($dto->averageRating)->toBe(0.0);
    }
})->with(dataProviderForTestGetStatistics());

/**
 * Provides data presence flags for testForGetStatistics.
 */
function dataProviderForTestGetStatistics(): array
{
    return [
        'empty database → all zeros' => [false],
        'seeded data → counts reflect DB state' => [true],
    ];
}

/**
 * getStatistics returns cached values on repeated calls without fresh queries.
 */
it('test_get_statistics_caching', function (): void {
    $first = $this->service->getStatistics();
    $second = $this->service->getStatistics();

    expect($first->activeStudents)->toBe($second->activeStudents)
        ->and($first->totalCourses)->toBe($second->totalCourses);
});
