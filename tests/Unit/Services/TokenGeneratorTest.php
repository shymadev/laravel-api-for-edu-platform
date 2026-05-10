<?php

declare(strict_types=1);

/**
 * Unit tests for TokenGenerator.
 */

use App\Enums\Role;
use App\Models\User\Role as RoleModel;
use App\Models\User\User;
use App\Services\TokenGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    RoleModel::upsert(
        [['id' => RoleModel::USER_ROLE_ID, 'role_name' => 'user']],
        ['id'],
    );

    $this->service = new TokenGenerator();
});

/**
 * generateAccessToken returns a non-empty plain text token string.
 */
it('test_generate_access_token_returns_string', function (): void {
    $user = User::create([
        'username' => 'token_user',
        'email' => 'token@example.com',
        'password_hash' => Hash::make('secret'),
        'role_id' => RoleModel::USER_ROLE_ID,
    ]);

    $token = $this->service->generateAccessToken($user, Role::USER);

    expect($token)->toBeString()->not->toBeEmpty();
});

/**
 * generateAccessToken stores the role as a token ability.
 */
it('test_generate_access_token_stores_role_ability', function (Role $role): void {
    RoleModel::upsert(
        [
            ['id' => RoleModel::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => RoleModel::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );

    $user = User::create([
        'username' => 'ability_user',
        'email' => 'ability@example.com',
        'password_hash' => Hash::make('secret'),
        'role_id' => RoleModel::USER_ROLE_ID,
    ]);

    $plainToken = $this->service->generateAccessToken($user, $role);

    // Token is in format id|plaintext — find the persisted token by user
    $dbToken = $user->tokens()->latest('id')->first();

    expect($dbToken)->not->toBeNull()
        ->and($dbToken->abilities)->toContain($role->value);
})->with([
    [Role::USER],
    [Role::ADMIN],
    [Role::MODERATOR],
]);

/**
 * Each call to generateAccessToken produces a unique token.
 */
it('test_generate_access_token_unique_each_call', function (): void {
    $user = User::create([
        'username' => 'multi_token',
        'email' => 'multi@example.com',
        'password_hash' => Hash::make('secret'),
        'role_id' => RoleModel::USER_ROLE_ID,
    ]);

    $token1 = $this->service->generateAccessToken($user, Role::USER);
    $token2 = $this->service->generateAccessToken($user, Role::USER);

    expect($token1)->not->toBe($token2);
});
