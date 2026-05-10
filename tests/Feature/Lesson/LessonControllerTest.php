<?php

declare(strict_types=1);

/**
 * Feature tests for lesson routes.
 */

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
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
        'title' => 'Test Course',
        'language' => 'English',
        'is_active' => true,
        'is_premium' => false,
    ]);

    $this->topic = Topic::create([
        'course_id' => $this->course->id,
        'title' => 'Test Topic',
        'is_active' => true,
    ]);
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeLessonUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeCourseLesson(Topic $topic, bool $isActive = true): Lesson
{
    return Lesson::create([
        'topic_id' => $topic->id,
        'title' => fake()->sentence(3),
        'weight' => 1,
        'is_active' => $isActive,
        'content' => [],
    ]);
}

// ─── GET /api/topics/{topic}/lessons ──────────────────────────────────────

/**
 * GET /api/topics/{topic}/lessons returns 200 with lessons.
 */
it('test_get_lessons_by_topic_returns_200', function (): void {
    makeCourseLesson($this->topic);
    makeCourseLesson($this->topic);

    $response = $this->getJson("/api/topics/{$this->topic->id}/lessons");

    $response->assertOk();
});

/**
 * GET /api/topics/{topic}/lessons returns 401 for premium course without auth.
 */
it('test_get_lessons_by_topic_returns_401_for_premium_without_auth', function (): void {
    $this->course->update(['is_premium' => true]);

    $response = $this->getJson("/api/topics/{$this->topic->id}/lessons");

    $response->assertUnauthorized();
});

// ─── GET /api/lessons/{lessonId} ──────────────────────────────────────────

/**
 * GET /api/lessons/{lessonId} returns 200 with lesson data.
 */
it('test_show_returns_200_for_existing_lesson', function (): void {
    $lesson = makeCourseLesson($this->topic);

    $response = $this->getJson("/api/lessons/{$lesson->id}");

    $response->assertOk()
        ->assertJsonFragment(['id' => $lesson->id]);
});

/**
 * GET /api/lessons/{lessonId} returns 404 for non-existent lesson.
 */
it('test_show_returns_404_for_unknown_lesson', function (): void {
    $response = $this->getJson('/api/lessons/999999');

    $response->assertNotFound();
});

/**
 * GET /api/lessons/{lessonId} returns 401 for premium course without auth.
 */
it('test_show_returns_401_for_premium_course_without_auth', function (): void {
    $this->course->update(['is_premium' => true]);
    $lesson = makeCourseLesson($this->topic);

    $response = $this->getJson("/api/lessons/{$lesson->id}");

    $response->assertUnauthorized();
});

// ─── POST /api/lessons ────────────────────────────────────────────────────

/**
 * POST /api/lessons by a moderator returns 201 with created lesson.
 * TTSService and AudioStorageService are mocked to avoid external dependencies.
 */
it('test_store_by_moderator_returns_201', function (): void {
    $mod = makeLessonUser(Role::MODERATOR_ROLE_ID);

    $this->mock(TTSService::class, function ($mock): void {
        $mock->shouldReceive('generateAudio')->andReturn(null);
    });
    $this->mock(AudioStorageService::class, function ($mock): void {
        $mock->shouldReceive('upload')->andReturn('https://storage.example.com/audio.wav');
        $mock->shouldReceive('delete')->andReturn(true);
        $mock->shouldReceive('exists')->andReturn(false);
    });

    $response = $this->actingAs($mod)->postJson('/api/lessons', [
        'topic_id' => $this->topic->id,
        'title' => 'New Lesson',
        'weight' => 1,
        'content' => '[]',
    ]);

    $response->assertCreated();
});

/**
 * POST /api/lessons by a regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makeLessonUser(Role::USER_ROLE_ID);

    $response = $this->actingAs($user)->postJson('/api/lessons', [
        'topic_id' => $this->topic->id,
        'title' => 'Forbidden Lesson',
        'weight' => 1,
        'content' => '[]',
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/lessons without auth returns 401.
 */
it('test_store_unauthenticated_returns_401', function (): void {
    $response = $this->postJson('/api/lessons', [
        'topic_id' => $this->topic->id,
        'title' => 'Ghost Lesson',
        'weight' => 1,
        'content' => '[]',
    ]);

    $response->assertUnauthorized();
});

// ─── PUT /api/lessons/{lesson} ────────────────────────────────────────────

/**
 * PUT /api/lessons/{lesson} by a moderator returns 200 with updated data.
 * TTSService and AudioStorageService are mocked to avoid external dependencies.
 */
it('test_update_by_moderator_returns_200', function (): void {
    $mod = makeLessonUser(Role::MODERATOR_ROLE_ID);
    $lesson = makeCourseLesson($this->topic);

    $this->mock(TTSService::class, function ($mock): void {
        $mock->shouldReceive('generateAudio')->andReturn(null);
    });
    $this->mock(AudioStorageService::class, function ($mock): void {
        $mock->shouldReceive('upload')->andReturn('https://storage.example.com/audio.wav');
        $mock->shouldReceive('delete')->andReturn(true);
        $mock->shouldReceive('exists')->andReturn(false);
    });

    $response = $this->actingAs($mod)->putJson("/api/lessons/{$lesson->id}", [
        'topic_id' => $this->topic->id,
        'title' => 'Updated Lesson',
        'weight' => 1,
        'content' => '[]',
    ]);

    $response->assertOk();
});

/**
 * PUT /api/lessons/{lesson} by a regular user returns 403.
 */
it('test_update_by_regular_user_returns_403', function (): void {
    $user = makeLessonUser();
    $lesson = makeCourseLesson($this->topic);

    $response = $this->actingAs($user)->putJson("/api/lessons/{$lesson->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/lessons/{lesson} ─────────────────────────────────────────

/**
 * DELETE /api/lessons/{lesson} by a moderator returns 204.
 */
it('test_destroy_by_moderator_returns_204', function (): void {
    $mod = makeLessonUser(Role::MODERATOR_ROLE_ID);
    $lesson = makeCourseLesson($this->topic);

    $response = $this->actingAs($mod)->deleteJson("/api/lessons/{$lesson->id}");

    $response->assertNoContent();
    expect(Lesson::find($lesson->id))->toBeNull();
});

/**
 * DELETE /api/lessons/{lesson} by a regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makeLessonUser();
    $lesson = makeCourseLesson($this->topic);

    $response = $this->actingAs($user)->deleteJson("/api/lessons/{$lesson->id}");

    $response->assertForbidden();
});

// ─── Publish / Unpublish ──────────────────────────────────────────────────

/**
 * PUT /api/lessons/{lesson}/publish by a moderator sets lesson active.
 */
it('test_publish_by_moderator_sets_lesson_active', function (): void {
    $mod = makeLessonUser(Role::MODERATOR_ROLE_ID);
    $lesson = makeCourseLesson($this->topic, isActive: false);

    $response = $this->actingAs($mod)->putJson("/api/lessons/{$lesson->id}/publish");

    $response->assertOk();
    expect(Lesson::find($lesson->id)->is_active)->toBeTrue();
});

/**
 * PUT /api/lessons/{lesson}/unpublish by a moderator sets lesson inactive.
 */
it('test_unpublish_by_moderator_sets_lesson_inactive', function (): void {
    $mod = makeLessonUser(Role::MODERATOR_ROLE_ID);
    $lesson = makeCourseLesson($this->topic, isActive: true);

    $response = $this->actingAs($mod)->putJson("/api/lessons/{$lesson->id}/unpublish");

    $response->assertOk();
    expect(Lesson::find($lesson->id)->is_active)->toBeFalse();
});
