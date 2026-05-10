<?php

declare(strict_types=1);

/**
 * Feature tests for change password routes.
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

function makeChangePasswordUser(string $password = 'secret123'): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make($password),
        'role_id' => Role::USER_ROLE_ID,
    ]);
}

// ─── POST /api/auth/change-password ──────────────────────────────────────

/**
 * POST /api/auth/change-password returns 200 when password changed successfully.
 */
it('test_change_password_returns_200_on_success', function (): void {
    $user = makeChangePasswordUser('oldpassword');

    $response = $this->actingAs($user)->postJson('/api/auth/change-password', [
        'old_password' => 'oldpassword',
        'new_password' => 'newpassword1',
        'new_password_confirmation' => 'newpassword1',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Password changed successfully']);
});

/**
 * POST /api/auth/change-password returns 400 when old password is wrong.
 * StandardChangePasswordStrategy returns false when Hash::check fails.
 */
it('test_change_password_returns_400_when_old_password_wrong', function (): void {
    $user = makeChangePasswordUser('correctpassword');

    $response = $this->actingAs($user)->postJson('/api/auth/change-password', [
        'old_password' => 'wrongpassword',
        'new_password' => 'newpassword1',
        'new_password_confirmation' => 'newpassword1',
    ]);

    $response->assertStatus(400)
        ->assertJsonFragment(['message' => 'Failed to change password']);
});

/**
 * POST /api/auth/change-password returns 422 when new password too short.
 */
it('test_change_password_returns_422_when_new_password_too_short', function (): void {
    $user = makeChangePasswordUser('oldpassword');

    $response = $this->actingAs($user)->postJson('/api/auth/change-password', [
        'old_password' => 'oldpassword',
        'new_password' => 'short',
        'new_password_confirmation' => 'short',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['new_password']);
});

/**
 * POST /api/auth/change-password returns 422 when passwords do not match.
 */
it('test_change_password_returns_422_when_passwords_do_not_match', function (): void {
    $user = makeChangePasswordUser('oldpassword');

    $response = $this->actingAs($user)->postJson('/api/auth/change-password', [
        'old_password' => 'oldpassword',
        'new_password' => 'newpassword1',
        'new_password_confirmation' => 'newpassword2',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['new_password']);
});

/**
 * POST /api/auth/change-password returns 401 without auth.
 */
it('test_change_password_returns_401_unauthenticated', function (): void {
    $response = $this->postJson('/api/auth/change-password', [
        'old_password' => 'oldpassword',
        'new_password' => 'newpassword',
        'new_password_confirmation' => 'newpassword',
    ]);

    $response->assertUnauthorized();
});
