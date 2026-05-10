<?php

declare(strict_types=1);

/**
 * Feature tests for course review routes.
 */

use App\Models\Education\Course;
use App\Models\Education\CourseReview;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Models\User\Role;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
            ['id' => Role::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => Role::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );

    $this->course = Course::create([
        'title' => 'Review Course',
        'language' => 'English',
        'is_active' => true,
        'is_premium' => false,
    ]);
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeReviewUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeReview(Course $course, User $user, int $rating = 4): CourseReview
{
    return CourseReview::create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'rating' => $rating,
        'review_text' => 'Great course!',
    ]);
}

function completeLessonForUser(User $user, Course $course): void
{
    $topic = Topic::create([
        'course_id' => $course->id,
        'title' => 'Topic',
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'topic_id' => $topic->id,
        'title' => 'Lesson',
        'weight' => 1,
        'is_active' => true,
        'content' => [],
    ]);

    UserCompletedLesson::create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
    ]);
}

// ─── GET /api/courses/{courseId}/reviews ──────────────────────────────────

/**
 * GET /api/courses/{courseId}/reviews returns 200 with reviews.
 */
it('test_index_returns_200_with_reviews', function (): void {
    $user = makeReviewUser();
    makeReview($this->course, $user);

    $response = $this->getJson("/api/courses/{$this->course->id}/reviews");

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

/**
 * GET /api/courses/{courseId}/reviews returns empty collection for course without reviews.
 */
it('test_index_returns_empty_for_course_without_reviews', function (): void {
    $response = $this->getJson("/api/courses/{$this->course->id}/reviews");

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

// ─── GET /api/courses/{courseId}/rating ───────────────────────────────────

/**
 * GET /api/courses/{courseId}/rating returns 200 with numeric rating.
 */
it('test_rating_returns_200_with_numeric_value', function (): void {
    $user = makeReviewUser();
    makeReview($this->course, $user, rating: 5);

    $response = $this->getJson("/api/courses/{$this->course->id}/rating");

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

/**
 * GET /api/courses/{courseId}/rating returns zero average when no reviews exist.
 */
it('test_rating_returns_zero_average_when_no_reviews', function (): void {
    $response = $this->getJson("/api/courses/{$this->course->id}/rating");

    $response->assertOk()
        ->assertJsonPath('data.average_rating', 0);
});

// ─── POST /api/courses/{courseId}/reviews ─────────────────────────────────

/**
 * POST /api/courses/{courseId}/reviews without auth returns 401.
 */
it('test_store_unauthenticated_returns_401', function (): void {
    $response = $this->postJson("/api/courses/{$this->course->id}/reviews", [
        'rating' => 4,
    ]);

    $response->assertUnauthorized();
});

/**
 * POST /api/courses/{courseId}/reviews without completed lesson returns 403.
 */
it('test_store_without_completed_lesson_returns_403', function (): void {
    $user = makeReviewUser();

    $response = $this->actingAs($user)->postJson("/api/courses/{$this->course->id}/reviews", [
        'rating' => 4,
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/courses/{courseId}/reviews with completed lesson returns 201.
 */
it('test_store_with_completed_lesson_returns_201', function (): void {
    $user = makeReviewUser();
    completeLessonForUser($user, $this->course);

    $response = $this->actingAs($user)->postJson("/api/courses/{$this->course->id}/reviews", [
        'rating' => 5,
        'review_text' => 'Amazing!',
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['message' => 'Review saved successfully']);
});

/**
 * POST /api/courses/{courseId}/reviews returns 422 when rating missing.
 */
it('test_store_returns_422_when_rating_missing', function (): void {
    $user = makeReviewUser();
    completeLessonForUser($user, $this->course);

    $response = $this->actingAs($user)->postJson("/api/courses/{$this->course->id}/reviews", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

// ─── DELETE /api/courses/{courseId}/reviews ───────────────────────────────

/**
 * DELETE /api/courses/{courseId}/reviews by owner returns 204.
 */
it('test_destroy_by_owner_returns_204', function (): void {
    $user = makeReviewUser();
    makeReview($this->course, $user);

    $response = $this->actingAs($user)->deleteJson("/api/courses/{$this->course->id}/reviews");

    $response->assertNoContent();
});

/**
 * DELETE /api/courses/{courseId}/reviews without auth returns 401.
 */
it('test_destroy_unauthenticated_returns_401', function (): void {
    $response = $this->deleteJson("/api/courses/{$this->course->id}/reviews");

    $response->assertUnauthorized();
});

// ─── DELETE /api/courses/reviews/{review} (admin) ─────────────────────────

/**
 * DELETE /api/courses/reviews/{review} by admin returns 204.
 */
it('test_destroy_admin_returns_204', function (): void {
    $admin = makeReviewUser(Role::ADMIN_ROLE_ID);
    $user = makeReviewUser();
    $review = makeReview($this->course, $user);

    $response = $this->actingAs($admin)->deleteJson("/api/courses/reviews/{$review->id}");

    $response->assertNoContent();
    expect(CourseReview::find($review->id))->toBeNull();
});

/**
 * DELETE /api/courses/reviews/{review} by regular user returns 403.
 */
it('test_destroy_admin_by_regular_user_returns_403', function (): void {
    $user = makeReviewUser();
    $other = makeReviewUser();
    $review = makeReview($this->course, $other);

    $response = $this->actingAs($user)->deleteJson("/api/courses/reviews/{$review->id}");

    $response->assertForbidden();
});
