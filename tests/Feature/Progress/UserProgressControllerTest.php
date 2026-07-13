<?php

declare(strict_types=1);

/**
 * Feature tests for user progress routes.
 */

use App\Models\Education\Course;
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
        'title' => 'Progress Course',
        'language' => 'English',
        'is_active' => true,
        'is_premium' => false,
    ]);

    $this->topic = Topic::create([
        'course_id' => $this->course->id,
        'title' => 'Progress Topic',
        'is_active' => true,
    ]);

    $this->lesson = Lesson::create([
        'topic_id' => $this->topic->id,
        'title' => 'Progress Lesson',
        'weight' => 1,
        'is_active' => true,
        'content' => [],
    ]);
});

/**
 * Create a user for progress route tests.
 *
 * @param int $roleId
 *
 * @return User
 */
function makeProgressUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

// ─── GET /api/user/completed-lessons ─────────────────────────────────────

/**
 * GET /api/user/completed-lessons returns 200 with completed lessons.
 */
it('test_completed_lessons_returns_200', function (): void {
    $user = makeProgressUser();
    UserCompletedLesson::create([
        'user_id' => $user->id,
        'lesson_id' => $this->lesson->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/user/completed-lessons');

    $response->assertOk();
});

/**
 * GET /api/user/completed-lessons returns 401 without auth.
 */
it('test_completed_lessons_returns_401_unauthenticated', function (): void {
    $response = $this->getJson('/api/user/completed-lessons');

    $response->assertUnauthorized();
});

// ─── POST /api/user/completed-lessons ────────────────────────────────────

/**
 * POST /api/user/completed-lessons marks a lesson as complete and returns 201.
 */
it('test_mark_lesson_complete_returns_201', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson('/api/user/completed-lessons', [
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['completed' => true]);

    expect(UserCompletedLesson::where('user_id', $user->id)->where('lesson_id', $this->lesson->id)->exists())->toBeTrue();
});

/**
 * POST /api/user/completed-lessons returns 200 when lesson is already completed.
 */
it('test_mark_lesson_complete_returns_200_when_already_completed', function (): void {
    $user = makeProgressUser();
    UserCompletedLesson::create([
        'user_id' => $user->id,
        'lesson_id' => $this->lesson->id,
    ]);

    $response = $this->actingAs($user)->postJson('/api/user/completed-lessons', [
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['alreadyCompleted' => true]);
});

/**
 * POST /api/user/completed-lessons returns 422 when lesson_id is missing.
 */
it('test_mark_lesson_complete_returns_422_when_lesson_id_missing', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson('/api/user/completed-lessons', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['lesson_id']);
});

/**
 * POST /api/user/completed-lessons returns 401 without auth.
 */
it('test_mark_lesson_complete_returns_401_unauthenticated', function (): void {
    $response = $this->postJson('/api/user/completed-lessons', [
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertUnauthorized();
});

// ─── GET /api/user/courses/{courseId}/progress ────────────────────────────

/**
 * GET /api/user/courses/{courseId}/progress returns 200 with course progress.
 */
it('test_course_progress_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->getJson("/api/user/courses/{$this->course->id}/progress");

    $response->assertOk();
});

/**
 * GET /api/user/courses/{courseId}/progress returns 401 without auth.
 */
it('test_course_progress_returns_401_unauthenticated', function (): void {
    $response = $this->getJson("/api/user/courses/{$this->course->id}/progress");

    $response->assertUnauthorized();
});

// ─── GET /api/user/courses/progress ──────────────────────────────────────

/**
 * GET /api/user/courses/progress returns 200 with all courses progress.
 */
it('test_courses_progress_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->getJson('/api/user/courses/progress');

    $response->assertOk();
});

/**
 * GET /api/user/courses/progress returns 401 without auth.
 */
it('test_courses_progress_returns_401_unauthenticated', function (): void {
    $response = $this->getJson('/api/user/courses/progress');

    $response->assertUnauthorized();
});

// ─── GET /api/user/statistics ─────────────────────────────────────────────

/**
 * GET /api/user/statistics returns 200 with user statistics.
 */
it('test_statistics_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->getJson('/api/user/statistics');

    $response->assertOk();
});

/**
 * GET /api/user/statistics returns 401 without auth.
 */
it('test_statistics_returns_401_unauthenticated', function (): void {
    $response = $this->getJson('/api/user/statistics');

    $response->assertUnauthorized();
});

// ─── GET /api/user/lessons/{lessonId}/block-progress ──────────────────────

/**
 * GET /api/user/lessons/{lessonId}/block-progress returns 200 with block progress.
 */
it('test_lesson_block_progress_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->getJson("/api/user/lessons/{$this->lesson->id}/block-progress");

    $response->assertOk();
});

/**
 * GET /api/user/lessons/{lessonId}/block-progress returns 401 without auth.
 */
it('test_lesson_block_progress_returns_401_unauthenticated', function (): void {
    $response = $this->getJson("/api/user/lessons/{$this->lesson->id}/block-progress");

    $response->assertUnauthorized();
});

// ─── POST /api/user/lesson-block-progress ────────────────────────────────

/**
 * POST /api/user/lesson-block-progress saves block progress and returns 201.
 */
it('test_save_block_progress_returns_201', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson('/api/user/lesson-block-progress', [
        'lesson_id' => $this->lesson->id,
        'block_index' => 0,
        'block_type' => 'text',
        'is_completed' => true,
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['saved' => true]);
});

/**
 * POST /api/user/lesson-block-progress ignores delayed partial state after completion.
 */
it('test_delayed_partial_save_does_not_overwrite_completed_block_state', function (): void {
    $user = makeProgressUser();

    $this->actingAs($user)->postJson('/api/user/lesson-block-progress', [
        'lesson_id' => $this->lesson->id,
        'block_index' => 0,
        'block_type' => 'matching',
        'is_completed' => true,
        'block_state' => ['final' => true],
    ])->assertCreated();

    $this->actingAs($user)->postJson('/api/user/lesson-block-progress', [
        'lesson_id' => $this->lesson->id,
        'block_index' => 0,
        'block_type' => 'matching',
        'is_completed' => false,
        'block_state' => ['draft' => true],
    ])->assertCreated();

    $response = $this->actingAs($user)->getJson("/api/user/lessons/{$this->lesson->id}/block-progress");

    $response->assertOk();
    expect($response->json('completed'))->toContain(0)
        ->and($response->json('states.0'))->toBe(['final' => true]);
});

/**
 * POST /api/user/lesson-block-progress returns 422 when required fields are missing.
 */
it('test_save_block_progress_returns_422_when_fields_missing', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson('/api/user/lesson-block-progress', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['lesson_id', 'block_index', 'block_type']);
});

/**
 * POST /api/user/lesson-block-progress returns 401 without auth.
 */
it('test_save_block_progress_returns_401_unauthenticated', function (): void {
    $response = $this->postJson('/api/user/lesson-block-progress', [
        'lesson_id' => $this->lesson->id,
        'block_index' => 0,
        'block_type' => 'text',
    ]);

    $response->assertUnauthorized();
});

// ─── POST /api/user/courses/{courseId}/stop ───────────────────────────────

/**
 * POST /api/user/courses/{courseId}/stop returns 200 with stopped status.
 */
it('test_stop_course_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson("/api/user/courses/{$this->course->id}/stop");

    $response->assertOk()
        ->assertJsonFragment(['stopped' => true]);
});

/**
 * POST /api/user/courses/{courseId}/stop returns 401 without auth.
 */
it('test_stop_course_returns_401_unauthenticated', function (): void {
    $response = $this->postJson("/api/user/courses/{$this->course->id}/stop");

    $response->assertUnauthorized();
});

// ─── POST /api/user/courses/{courseId}/resume ─────────────────────────────

/**
 * POST /api/user/courses/{courseId}/resume returns 200 with resumed status.
 */
it('test_resume_course_returns_200', function (): void {
    $user = makeProgressUser();

    $response = $this->actingAs($user)->postJson("/api/user/courses/{$this->course->id}/resume");

    $response->assertOk()
        ->assertJsonFragment(['resumed' => true]);
});

/**
 * POST /api/user/courses/{courseId}/resume returns 401 without auth.
 */
it('test_resume_course_returns_401_unauthenticated', function (): void {
    $response = $this->postJson("/api/user/courses/{$this->course->id}/resume");

    $response->assertUnauthorized();
});
