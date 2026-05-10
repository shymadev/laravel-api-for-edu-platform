<?php

declare(strict_types=1);

/**
 * Feature tests for user management routes (admin only).
 */

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

function makeUserManagementUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

// ─── GET /api/users ───────────────────────────────────────────────────────

/**
 * GET /api/users by admin returns 200 with users list.
 */
it('test_index_by_admin_returns_200', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    makeUserManagementUser();
    makeUserManagementUser();

    $response = $this->actingAs($admin)->getJson('/api/users');

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

/**
 * GET /api/users by regular user returns 403.
 */
it('test_index_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();

    $response = $this->actingAs($user)->getJson('/api/users');

    $response->assertForbidden();
});

/**
 * GET /api/users without auth returns 401.
 */
it('test_index_unauthenticated_returns_401', function (): void {
    $response = $this->getJson('/api/users');

    $response->assertUnauthorized();
});

// ─── GET /api/users/{id} ──────────────────────────────────────────────────

/**
 * GET /api/users/{id} by admin returns 200 with user data.
 */
it('test_show_by_admin_returns_200', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    $target = makeUserManagementUser();

    $response = $this->actingAs($admin)->getJson("/api/users/{$target->id}");

    $response->assertOk()
        ->assertJsonFragment(['id' => $target->id]);
});

/**
 * GET /api/users/{id} returns 404 for non-existent user.
 */
it('test_show_returns_404_for_unknown_user', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);

    $response = $this->actingAs($admin)->getJson('/api/users/999999');

    $response->assertNotFound();
});

/**
 * GET /api/users/{id} by regular user returns 403.
 */
it('test_show_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();
    $target = makeUserManagementUser();

    $response = $this->actingAs($user)->getJson("/api/users/{$target->id}");

    $response->assertForbidden();
});

// ─── POST /api/users ──────────────────────────────────────────────────────

/**
 * @covers UserController::store
 *
 * POST /api/users by admin returns 201 with created user.
 */
it('test_store_by_admin_returns_201', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);

    $response = $this->actingAs($admin)->postJson('/api/users', [
        'username' => 'newadminuser',
        'email' => 'newadmin@example.com',
        'password' => 'securepass',
        'roleId' => Role::USER_ROLE_ID,
    ]);

    $response->assertCreated();
});

/**
 * POST /api/users by regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();

    $response = $this->actingAs($user)->postJson('/api/users', [
        'username' => 'hacker',
        'email' => 'hacker@example.com',
        'password' => 'securepass',
        'roleId' => Role::USER_ROLE_ID,
    ]);

    $response->assertForbidden();
});

// ─── PUT /api/users/{user} ────────────────────────────────────────────────

/**
 * PUT /api/users/{user} by admin returns 200 with updated data.
 */
it('test_update_by_admin_returns_200', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    $target = makeUserManagementUser();

    $response = $this->actingAs($admin)->putJson("/api/users/{$target->id}", [
        'username' => 'updatedname',
    ]);

    $response->assertOk();
});

/**
 * PUT /api/users/{user} by regular user returns 403.
 */
it('test_update_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();
    $target = makeUserManagementUser();

    $response = $this->actingAs($user)->putJson("/api/users/{$target->id}", [
        'username' => 'hijacked',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/users/{user} ─────────────────────────────────────────────

/**
 * DELETE /api/users/{user} by admin returns 204.
 */
it('test_destroy_by_admin_returns_204', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    $target = makeUserManagementUser();

    $response = $this->actingAs($admin)->deleteJson("/api/users/{$target->id}");

    $response->assertNoContent();
});

/**
 * DELETE /api/users/{user} by regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();
    $target = makeUserManagementUser();

    $response = $this->actingAs($user)->deleteJson("/api/users/{$target->id}");

    $response->assertForbidden();
});

// ─── PUT /api/users/{user}/block ──────────────────────────────────────────

/**
 * PUT /api/users/{user}/block by admin blocks the user.
 */
it('test_block_by_admin_blocks_user', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    $target = makeUserManagementUser();

    $response = $this->actingAs($admin)->putJson("/api/users/{$target->id}/block");

    $response->assertOk();
    expect(User::find($target->id)->is_blocked)->toBeTrue();
});

/**
 * PUT /api/users/{user}/block by regular user returns 403.
 */
it('test_block_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();
    $target = makeUserManagementUser();

    $response = $this->actingAs($user)->putJson("/api/users/{$target->id}/block");

    $response->assertForbidden();
});

// ─── PUT /api/users/{user}/unblock ────────────────────────────────────────

/**
 * PUT /api/users/{user}/unblock by admin unblocks the user.
 */
it('test_unblock_by_admin_unblocks_user', function (): void {
    $admin = makeUserManagementUser(Role::ADMIN_ROLE_ID);
    $target = makeUserManagementUser();
    $target->update(['is_blocked' => true]);

    $response = $this->actingAs($admin)->putJson("/api/users/{$target->id}/unblock");

    $response->assertOk();
    expect(User::find($target->id)->is_blocked)->toBeFalse();
});

/**
 * PUT /api/users/{user}/unblock by regular user returns 403.
 */
it('test_unblock_by_regular_user_returns_403', function (): void {
    $user = makeUserManagementUser();
    $target = makeUserManagementUser();
    $target->update(['is_blocked' => true]);

    $response = $this->actingAs($user)->putJson("/api/users/{$target->id}/unblock");

    $response->assertForbidden();
});
