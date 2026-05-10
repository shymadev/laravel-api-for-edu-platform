<?php

declare(strict_types=1);

/**
 * Unit tests for CourseReviewService.
 */

use App\DTO\Review\CreateReviewDTO;
use App\Models\Education\Course;
use App\Models\Education\CourseReview;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\CourseReviewService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new CourseReviewService();

    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
            ['id' => Role::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => Role::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );

    $this->course = Course::create([
        'title' => 'Test Course',
        'description' => 'Desc',
        'language' => 'English',
        'is_active' => true,
        'is_premium' => false,
    ]);
});

function makeReviewServiceUser(): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
    ]);
}

/**
 * getReviewsByCourse lists reviews newest-first, or none when the course has none.
 */
it('test_get_reviews_by_course', function (int $count): void {
    for ($i = 0; $i < $count; $i++) {
        $user = makeReviewServiceUser();
        CourseReview::create(['course_id' => $this->course->id, 'user_id' => $user->id, 'rating' => 4]);
    }

    expect($this->service->getReviewsByCourse($this->course->id))->toHaveCount($count);
})->with(dataProviderForTestGetReviewsByCourse());

/**
 * Provides review counts for testForGetReviewsByCourse.
 */
function dataProviderForTestGetReviewsByCourse(): array
{
    return [
        'no reviews → empty collection' => [0],
        'two reviews → two items' => [2],
    ];
}

/**
 * getCourseRating yields zeros with no data, or average and totals from reviews.
 */
it('test_get_course_rating', function (array $ratings, float $expectedAvg, int $expectedTotal): void {
    foreach ($ratings as $rating) {
        $user = makeReviewServiceUser();
        CourseReview::create(['course_id' => $this->course->id, 'user_id' => $user->id, 'rating' => $rating]);
    }

    $result = $this->service->getCourseRating($this->course->id);

    expect($result['total_reviews'])->toBe($expectedTotal)
        ->and($result['average_rating'])->toBe($expectedAvg);
})->with(dataProviderForTestGetCourseRating());

/**
 * Provides rating arrays and expected results for testForGetCourseRating.
 */
function dataProviderForTestGetCourseRating(): array
{
    return [
        'no reviews → zeros' => [[], 0.0, 0],
        'ratings 5 and 3 → average 4.0' => [[5, 3], 4.0, 2],
    ];
}

/**
 * createOrUpdateReview inserts once, then updates the same user+course row.
 */
it('test_create_or_update_review', function (bool $isUpdate): void {
    $user = makeReviewServiceUser();

    $this->service->createOrUpdateReview(
        new CreateReviewDTO(userId: $user->id, courseId: $this->course->id, rating: 3, reviewText: 'Original'),
    );

    if ($isUpdate) {
        $updated = $this->service->createOrUpdateReview(
            new CreateReviewDTO(userId: $user->id, courseId: $this->course->id, rating: 5, reviewText: 'Updated'),
        );

        $count = CourseReview::where('user_id', $user->id)->where('course_id', $this->course->id)->count();
        expect($count)->toBe(1)->and($updated->rating)->toBe(5);
    } else {
        $count = CourseReview::where('user_id', $user->id)->where('course_id', $this->course->id)->count();
        expect($count)->toBe(1);
    }
})->with(dataProviderForTestCreateOrUpdateReview());

/**
 * Provides update flag scenarios for testForCreateOrUpdateReview.
 */
function dataProviderForTestCreateOrUpdateReview(): array
{
    return [
        'first call creates a new review' => [false],
        'second call updates the existing row' => [true],
    ];
}

/**
 * deleteReviewByUser deletes the row when present, or returns false if missing.
 */
it('test_delete_review_by_user', function (bool $reviewExists): void {
    if ($reviewExists) {
        $user = makeReviewServiceUser();
        CourseReview::create(['course_id' => $this->course->id, 'user_id' => $user->id, 'rating' => 4]);

        $result = $this->service->deleteReviewByUser($user->id, $this->course->id);

        expect($result)->toBeTrue()
            ->and(CourseReview::where('user_id', $user->id)->count())->toBe(0);
    } else {
        expect($this->service->deleteReviewByUser(PHP_INT_MAX, $this->course->id))->toBeFalse();
    }
})->with(dataProviderForTestDeleteReviewByUser());

/**
 * Provides existence flags for testForDeleteReviewByUser.
 */
function dataProviderForTestDeleteReviewByUser(): array
{
    return [
        'existing review is deleted → true' => [true],
        'no review → false' => [false],
    ];
}

/**
 * deleteReviewByAdmin removes the model instance and clears it from the DB.
 */
it('test_delete_review_by_admin', function (): void {
    $user = makeReviewServiceUser();
    $review = CourseReview::create(['course_id' => $this->course->id, 'user_id' => $user->id, 'rating' => 5]);

    expect($this->service->deleteReviewByAdmin($review))->toBeTrue()
        ->and(CourseReview::find($review->id))->toBeNull();
});
