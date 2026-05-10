<?php

declare(strict_types=1);

/**
 * Feature tests for topic routes.
 */

use App\Models\Education\Course;
use App\Models\Education\Topic;
use App\Models\User\Role;
use App\Models\User\User;
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
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeTopicUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeCourseTopic(Course $course, bool $isActive = false): Topic
{
    return Topic::create([
        'course_id' => $course->id,
        'title' => fake()->sentence(3),
        'is_active' => $isActive,
    ]);
}

// ─── GET /api/courses/{course}/topics ─────────────────────────────────────

/**
 * GET /api/courses/{course}/topics returns 200 with topics list.
 */
it('test_get_topics_by_course_returns_200', function (): void {
    makeCourseTopic($this->course);
    makeCourseTopic($this->course);

    $response = $this->getJson("/api/courses/{$this->course->id}/topics");

    $response->assertOk()
        ->assertJsonStructure(['data']);
    expect(count($response->json('data')))->toBe(2);
});

/**
 * GET /api/courses/{course}/topics returns empty list for course with no topics.
 */
it('test_get_topics_by_course_returns_empty_when_no_topics', function (): void {
    $response = $this->getJson("/api/courses/{$this->course->id}/topics");

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

// ─── GET /api/topics/{topic} ──────────────────────────────────────────────

/**
 * GET /api/topics/{topic} returns 200 with topic data.
 */
it('test_show_returns_200_for_existing_topic', function (): void {
    $topic = makeCourseTopic($this->course);

    $response = $this->getJson("/api/topics/{$topic->id}");

    $response->assertOk()
        ->assertJsonFragment(['id' => $topic->id]);
});

/**
 * GET /api/topics/{topic} returns 404 for non-existent topic.
 */
it('test_show_returns_404_for_unknown_topic', function (): void {
    $response = $this->getJson('/api/topics/999999');

    $response->assertNotFound();
});

// ─── POST /api/topics ─────────────────────────────────────────────────────

/**
 * POST /api/topics by a moderator returns 201 with created topic.
 */
it('test_store_by_moderator_returns_201', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);

    $response = $this->actingAs($mod)->postJson('/api/topics', [
        'course_id' => $this->course->id,
        'title' => 'New Topic',
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['title' => 'New Topic']);
});

/**
 * POST /api/topics by a regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makeTopicUser(Role::USER_ROLE_ID);

    $response = $this->actingAs($user)->postJson('/api/topics', [
        'course_id' => $this->course->id,
        'title' => 'Forbidden Topic',
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/topics without auth returns 401.
 */
it('test_store_unauthenticated_returns_401', function (): void {
    $response = $this->postJson('/api/topics', [
        'course_id' => $this->course->id,
        'title' => 'Ghost Topic',
    ]);

    $response->assertUnauthorized();
});

/**
 * POST /api/topics returns 422 when required fields missing.
 */
it('test_store_returns_422_when_fields_missing', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);

    $response = $this->actingAs($mod)->postJson('/api/topics', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['course_id', 'title']);
});

// ─── PUT /api/topics/{topic} ──────────────────────────────────────────────

/**
 * PUT /api/topics/{topic} by a moderator returns 200 with updated data.
 */
it('test_update_by_moderator_returns_200', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);
    $topic = makeCourseTopic($this->course);

    $response = $this->actingAs($mod)->putJson("/api/topics/{$topic->id}", [
        'title' => 'Updated Topic',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Updated Topic']);
});

/**
 * PUT /api/topics/{topic} by a regular user returns 403.
 */
it('test_update_by_regular_user_returns_403', function (): void {
    $user = makeTopicUser();
    $topic = makeCourseTopic($this->course);

    $response = $this->actingAs($user)->putJson("/api/topics/{$topic->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/topics/{topic} ───────────────────────────────────────────

/**
 * DELETE /api/topics/{topic} by a moderator returns 204.
 */
it('test_destroy_by_moderator_returns_204', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);
    $topic = makeCourseTopic($this->course);

    $response = $this->actingAs($mod)->deleteJson("/api/topics/{$topic->id}");

    $response->assertNoContent();
    expect(Topic::find($topic->id))->toBeNull();
});

/**
 * DELETE /api/topics/{topic} by a regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makeTopicUser();
    $topic = makeCourseTopic($this->course);

    $response = $this->actingAs($user)->deleteJson("/api/topics/{$topic->id}");

    $response->assertForbidden();
});

// ─── Publish / Unpublish ──────────────────────────────────────────────────

/**
 * PUT /api/topics/{topic}/publish by a moderator sets topic active.
 */
it('test_publish_by_moderator_sets_topic_active', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);
    $topic = makeCourseTopic($this->course, isActive: false);

    $response = $this->actingAs($mod)->putJson("/api/topics/{$topic->id}/publish");

    $response->assertOk();
    expect(Topic::find($topic->id)->is_active)->toBeTrue();
});

/**
 * PUT /api/topics/{topic}/unpublish by a moderator sets topic inactive.
 */
it('test_unpublish_by_moderator_sets_topic_inactive', function (): void {
    $mod = makeTopicUser(Role::MODERATOR_ROLE_ID);
    $topic = makeCourseTopic($this->course, isActive: true);

    $response = $this->actingAs($mod)->putJson("/api/topics/{$topic->id}/unpublish");

    $response->assertOk();
    expect(Topic::find($topic->id)->is_active)->toBeFalse();
});
