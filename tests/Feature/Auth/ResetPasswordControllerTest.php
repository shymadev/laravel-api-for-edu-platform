<?php

declare(strict_types=1);

/**
 * Feature tests for password reset routes.
 */

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

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

function makeResetUser(): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
    ]);
}

// ─── POST /api/auth/forgot-password ──────────────────────────────────────

/**
 * POST /api/auth/forgot-password returns 200 with neutral message for existing email.
 */
it('test_forgot_password_returns_200_for_existing_user', function (): void {
    Mail::fake();
    $user = makeResetUser();

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['message']);
});

/**
 * POST /api/auth/forgot-password returns 200 with neutral message for non-existent email.
 * (Security: does not reveal whether email exists).
 */
it('test_forgot_password_returns_200_for_non_existent_email', function (): void {
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['message']);
});

/**
 * POST /api/auth/forgot-password returns 422 when email is missing.
 */
it('test_forgot_password_returns_422_when_email_missing', function (): void {
    $response = $this->postJson('/api/auth/forgot-password', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

/**
 * POST /api/auth/forgot-password returns 422 when email is invalid.
 */
it('test_forgot_password_returns_422_when_email_invalid', function (): void {
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'not-an-email',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ─── POST /api/auth/reset-password ───────────────────────────────────────

/**
 * POST /api/auth/reset-password returns 422 when required fields are missing.
 */
it('test_reset_password_returns_422_when_fields_missing', function (array $payload, array $errors): void {
    $response = $this->postJson('/api/auth/reset-password', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing token' => [
        ['email' => 'u@example.com', 'password' => 'newpassword', 'password_confirmation' => 'newpassword'],
        ['token'],
    ],
    'missing email' => [
        ['token' => 'abc', 'password' => 'newpassword', 'password_confirmation' => 'newpassword'],
        ['email'],
    ],
    'missing password' => [
        ['token' => 'abc', 'email' => 'u@example.com'],
        ['password'],
    ],
    'password too short' => [
        ['token' => 'abc', 'email' => 'u@example.com', 'password' => 'short', 'password_confirmation' => 'short'],
        ['password'],
    ],
    'passwords do not match' => [
        ['token' => 'abc', 'email' => 'u@example.com', 'password' => 'newpassword1', 'password_confirmation' => 'newpassword2'],
        ['password'],
    ],
]);

/**
 * POST /api/auth/reset-password returns 422 with invalid token.
 */
it('test_reset_password_returns_422_with_invalid_token', function (): void {
    $user = makeResetUser();

    $response = $this->postJson('/api/auth/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['message']);
});
