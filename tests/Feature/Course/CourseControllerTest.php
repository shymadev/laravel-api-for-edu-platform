<?php

declare(strict_types=1);

/**
 * Feature tests for course routes.
 */

use App\Models\Education\Course;
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
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeFeatureCourse(bool $isActive = true, bool $isArchived = false): Course
{
    return Course::create([
        'title' => fake()->sentence(3),
        'language' => 'English',
        'is_active' => $isActive,
        'is_premium' => false,
        'is_archived' => $isArchived,
    ]);
}

// ─── GET /api/courses ─────────────────────────────────────────────────────

/**
 * GET /api/courses returns 200 with a list of non-archived courses.
 */
it('test_index_returns_200_with_courses', function (): void {
    makeFeatureCourse();
    makeFeatureCourse();

    $response = $this->getJson('/api/courses');

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

/**
 * GET /api/courses excludes archived courses by default.
 */
it('test_index_excludes_archived_courses_by_default', function (): void {
    $active = makeFeatureCourse(isActive: true, isArchived: false);
    makeFeatureCourse(isActive: false, isArchived: true);

    $response = $this->getJson('/api/courses');

    $ids = collect($response->json('data'))->pluck('id')->toArray();
    expect($ids)->toContain($active->id)
        ->and(count($ids))->toBe(1);
});

/**
 * GET /api/courses with show_archived=true includes archived courses.
 */
it('test_index_shows_archived_when_requested', function (): void {
    makeFeatureCourse(isActive: true, isArchived: false);
    makeFeatureCourse(isActive: false, isArchived: true);

    $response = $this->getJson('/api/courses?show_archived=1');

    $response->assertOk();
    expect(count($response->json('data')))->toBe(2);
});

// ─── GET /api/courses/{id} ────────────────────────────────────────────────

/**
 * GET /api/courses/{id} returns 200 with course data.
 */
it('test_show_returns_200_for_existing_course', function (): void {
    $course = makeFeatureCourse();

    $response = $this->getJson("/api/courses/{$course->id}");

    $response->assertOk()
        ->assertJsonFragment(['id' => $course->id]);
});

/**
 * GET /api/courses/{id} returns 404 for a non-existent course.
 */
it('test_show_returns_404_for_unknown_course', function (): void {
    $response = $this->getJson('/api/courses/999999');

    $response->assertNotFound();
});

// ─── POST /api/courses ─────────────────────────────────────────────────────

/**
 * POST /api/courses by a moderator returns 201 with created course.
 */
it('test_store_by_moderator_returns_201', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);

    $response = $this->actingAs($mod)->postJson('/api/courses', [
        'title' => 'New Course',
        'language' => 'English',
        'is_premium' => 'false',
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['title' => 'New Course']);
});

/**
 * POST /api/courses by a regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makeUser(Role::USER_ROLE_ID);

    $response = $this->actingAs($user)->postJson('/api/courses', [
        'title' => 'Forbidden Course',
        'language' => 'English',
        'is_premium' => false,
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/courses without auth returns 401.
 */
it('test_store_unauthenticated_returns_401', function (): void {
    $response = $this->postJson('/api/courses', [
        'title' => 'Ghost Course',
        'language' => 'English',
    ]);

    $response->assertUnauthorized();
});

// ─── PUT /api/courses/{id} ────────────────────────────────────────────────

/**
 * PUT /api/courses/{id} by a moderator returns 200 with updated data.
 */
it('test_update_by_moderator_returns_200', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);
    $course = makeFeatureCourse();

    $response = $this->actingAs($mod)->putJson("/api/courses/{$course->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Updated Title']);
});

/**
 * PUT /api/courses/{id} by a regular user returns 403.
 */
it('test_update_by_regular_user_returns_403', function (): void {
    $user = makeUser();
    $course = makeFeatureCourse();

    $response = $this->actingAs($user)->putJson("/api/courses/{$course->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/courses/{id} ─────────────────────────────────────────────

/**
 * DELETE /api/courses/{id} by an admin returns 204.
 */
it('test_destroy_by_admin_returns_204', function (): void {
    $admin = makeUser(Role::ADMIN_ROLE_ID);
    $course = makeFeatureCourse();

    $response = $this->actingAs($admin)->deleteJson("/api/courses/{$course->id}");

    $response->assertNoContent();
    expect(Course::find($course->id))->toBeNull();
});

/**
 * DELETE /api/courses/{id} by a regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makeUser();
    $course = makeFeatureCourse();

    $response = $this->actingAs($user)->deleteJson("/api/courses/{$course->id}");

    $response->assertForbidden();
});

// ─── Publish / Unpublish ──────────────────────────────────────────────────

/**
 * PUT /api/courses/{id}/publish by a moderator activates the course.
 */
it('test_publish_by_moderator_sets_course_active', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);
    $course = makeFeatureCourse(isActive: false);

    $response = $this->actingAs($mod)->putJson("/api/courses/{$course->id}/publish");

    $response->assertOk();
    expect(Course::find($course->id)->is_active)->toBeTrue();
});

/**
 * PUT /api/courses/{id}/unpublish by a moderator deactivates the course.
 */
it('test_unpublish_by_moderator_sets_course_inactive', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);
    $course = makeFeatureCourse(isActive: true);

    $response = $this->actingAs($mod)->putJson("/api/courses/{$course->id}/unpublish");

    $response->assertOk();
    expect(Course::find($course->id)->is_active)->toBeFalse();
});

// ─── Archive / Unarchive ─────────────────────────────────────────────────

/**
 * PUT /api/courses/{id}/archive by a moderator archives the course.
 */
it('test_archive_by_moderator_archives_course', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);
    $course = makeFeatureCourse(isActive: true);

    $response = $this->actingAs($mod)->putJson("/api/courses/{$course->id}/archive");

    $response->assertOk();
    $updated = Course::find($course->id);
    expect($updated->is_archived)->toBeTrue()
        ->and($updated->is_active)->toBeFalse();
});

/**
 * PUT /api/courses/{id}/unarchive by a moderator unarchives the course.
 */
it('test_unarchive_by_moderator_unarchives_course', function (): void {
    $mod = makeUser(Role::MODERATOR_ROLE_ID);
    $course = makeFeatureCourse(isActive: false, isArchived: true);

    $response = $this->actingAs($mod)->putJson("/api/courses/{$course->id}/unarchive");

    $response->assertOk();
    expect(Course::find($course->id)->is_archived)->toBeFalse();
});
