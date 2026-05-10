<?php

declare(strict_types=1);

/**
 * Feature tests for logs routes (admin only).
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

function makeLogsUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

// ─── GET /api/logs ────────────────────────────────────────────────────────

/**
 * GET /api/logs by admin returns 200 with logs collection.
 */
it('test_index_by_admin_returns_200', function (): void {
    $admin = makeLogsUser(Role::ADMIN_ROLE_ID);

    $response = $this->actingAs($admin)->getJson('/api/logs');

    $response->assertOk();
});

/**
 * GET /api/logs by regular user returns 403.
 */
it('test_index_by_regular_user_returns_403', function (): void {
    $user = makeLogsUser(Role::USER_ROLE_ID);

    $response = $this->actingAs($user)->getJson('/api/logs');

    $response->assertForbidden();
});

/**
 * GET /api/logs by moderator returns 403.
 */
it('test_index_by_moderator_returns_403', function (): void {
    $mod = makeLogsUser(Role::MODERATOR_ROLE_ID);

    $response = $this->actingAs($mod)->getJson('/api/logs');

    $response->assertForbidden();
});

/**
 * GET /api/logs without auth returns 401.
 */
it('test_index_unauthenticated_returns_401', function (): void {
    $response = $this->getJson('/api/logs');

    $response->assertUnauthorized();
});

/**
 * GET /api/logs with level filter returns 200.
 */
it('test_index_with_level_filter_returns_200', function (): void {
    $admin = makeLogsUser(Role::ADMIN_ROLE_ID);

    $response = $this->actingAs($admin)->getJson('/api/logs?level=info');

    $response->assertOk();
});

/**
 * GET /api/logs with user_id filter returns 200.
 */
it('test_index_with_user_id_filter_returns_200', function (): void {
    $admin = makeLogsUser(Role::ADMIN_ROLE_ID);
    $target = makeLogsUser();

    $response = $this->actingAs($admin)->getJson("/api/logs?user_id={$target->id}");

    $response->assertOk();
});

/**
 * GET /api/logs with message_like filter returns 200.
 */
it('test_index_with_message_filter_returns_200', function (): void {
    $admin = makeLogsUser(Role::ADMIN_ROLE_ID);

    $response = $this->actingAs($admin)->getJson('/api/logs?message_like=login');

    $response->assertOk();
});
