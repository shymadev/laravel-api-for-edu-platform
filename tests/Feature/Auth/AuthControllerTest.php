<?php

declare(strict_types=1);

/**
 * Feature tests for authentication routes.
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

// ─── Register ─────────────────────────────────────────────────────────────

/**
 * POST /api/auth/register with valid data returns 201 with user and token.
 */
it('test_register_with_valid_data_returns_201', function (): void {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'securepass',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'username', 'email'],
            'token',
        ]);
});

/**
 * POST /api/auth/register returns 422 when required fields are missing.
 */
it('test_register_returns_422_when_fields_missing', function (array $payload, array $errors): void {
    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing email' => [['name' => 'user', 'password' => 'pass1234'], ['email']],
    'missing name' => [['email' => 'u@example.com', 'password' => 'pass1234'], ['name']],
    'missing password' => [['name' => 'user', 'email' => 'u@example.com'], ['password']],
    'short password' => [['name' => 'user', 'email' => 'u@example.com', 'password' => 'short'], ['password']],
]);

/**
 * POST /api/auth/register returns 422 when email is already taken.
 */
it('test_register_returns_422_on_duplicate_email', function (): void {
    User::create([
        'username' => 'existing',
        'email' => 'taken@example.com',
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'another',
        'email' => 'taken@example.com',
        'password' => 'securepass',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ─── Login ────────────────────────────────────────────────────────────────

/**
 * POST /api/auth/login with correct email returns 200 with token.
 */
it('test_login_with_valid_email_returns_200', function (): void {
    User::create([
        'username' => 'loginuser',
        'email' => 'login@example.com',
        'password_hash' => Hash::make('secret123'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'login@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'username', 'email'],
            'token',
        ]);
});

/**
 * POST /api/auth/login with correct username returns 200 with token.
 */
it('test_login_with_valid_username_returns_200', function (): void {
    User::create([
        'username' => 'usernamelogin',
        'email' => 'usernamelogin@example.com',
        'password_hash' => Hash::make('secret123'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'usernamelogin',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token']);
});

/**
 * POST /api/auth/login with wrong password returns 401.
 */
it('test_login_with_wrong_password_returns_401', function (): void {
    User::create([
        'username' => 'wrongpw',
        'email' => 'wrongpw@example.com',
        'password_hash' => Hash::make('correct'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'wrongpw@example.com',
        'password' => 'incorrect',
    ]);

    $response->assertUnauthorized();
});

/**
 * POST /api/auth/login for a blocked user returns 403.
 */
it('test_login_blocked_user_returns_403', function (): void {
    User::create([
        'username' => 'blocked',
        'email' => 'blocked@example.com',
        'password_hash' => Hash::make('secret123'),
        'role_id' => Role::USER_ROLE_ID,
        'is_blocked' => true,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'blocked@example.com',
        'password' => 'secret123',
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/auth/login returns 422 when required fields are missing.
 */
it('test_login_returns_422_when_fields_missing', function (array $payload, array $errors): void {
    $response = $this->postJson('/api/auth/login', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'missing login' => [['password' => 'pass1234'], ['login']],
    'missing password' => [['login' => 'user@example.com'], ['password']],
]);

// ─── Logout ───────────────────────────────────────────────────────────────

/**
 * POST /api/auth/logout for authenticated user returns 200.
 */
it('test_logout_authenticated_user_returns_200', function (): void {
    $user = User::create([
        'username' => 'logoutuser',
        'email' => 'logout@example.com',
        'password_hash' => Hash::make('secret'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->actingAs($user)->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Logged out successfully']);
});

/**
 * POST /api/auth/logout without auth returns 401.
 */
it('test_logout_unauthenticated_returns_401', function (): void {
    $response = $this->postJson('/api/auth/logout');

    $response->assertUnauthorized();
});

/**
 * GET /api/auth/user returns authenticated user data.
 */
it('test_current_user_returns_user_data', function (): void {
    $user = User::create([
        'username' => 'currentuser',
        'email' => 'current@example.com',
        'password_hash' => Hash::make('secret'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $response = $this->actingAs($user)->getJson('/api/auth/user');

    $response->assertOk()
        ->assertJsonFragment(['email' => 'current@example.com']);
});

/**
 * GET /api/auth/user without auth returns 401.
 */
it('test_current_user_unauthenticated_returns_401', function (): void {
    $response = $this->getJson('/api/auth/user');

    $response->assertUnauthorized();
});
