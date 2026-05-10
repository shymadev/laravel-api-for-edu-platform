<?php

declare(strict_types=1);

/**
 * Unit tests for UserService.
 */

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\MailService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->mail = Mockery::mock(MailService::class);
    $this->service = new UserService($this->mail);

    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
            ['id' => Role::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => Role::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );
});

/**
 * createUser hashes the password, applies role_id from DTO or defaults to user role.
 */
it('test_create_user', function (?int $roleId, int $expectedRoleId): void {
    $dto = new CreateUserDTO(
        username: 'newuser',
        email: 'new@example.com',
        password: 'secret123',
        roleId: $roleId,
    );

    $user = $this->service->createUser($dto);

    expect($user->id)->not->toBeNull()
        ->and($user->username)->toBe('newuser')
        ->and($user->role_id)->toBe($expectedRoleId)
        ->and(Hash::check('secret123', $user->password_hash))->toBeTrue();
})->with(dataProviderForTestCreateUser());

/**
 * Provides role ID values and expected results for testForCreateUser.
 */
function dataProviderForTestCreateUser(): array
{
    return [
        'with explicit role' => [Role::ADMIN_ROLE_ID, Role::ADMIN_ROLE_ID],
        'null role defaults to user role (1)' => [null, 1],
    ];
}

/**
 * updateUser applies non-null fields; email changes trigger token revocation.
 */
it('test_update_user', function (bool $changeEmail): void {
    $user = makeServiceUser();

    if ($changeEmail) {
        $dto = new UpdateUserDTO(username: null, email: 'updated@example.com', password: null, roleId: null);
        $result = $this->service->updateUser($user, $dto);
        expect($result->email)->toBe('updated@example.com');
    } else {
        $dto = new UpdateUserDTO(username: 'updatedname', email: null, password: null, roleId: null);
        $result = $this->service->updateUser($user, $dto);
        expect($result->username)->toBe('updatedname');
    }
})->with(dataProviderForTestUpdateUser());

/**
 * Provides update scenario flags for testForUpdateUser.
 */
function dataProviderForTestUpdateUser(): array
{
    return [
        'email change applied' => [true],
        'username change applied' => [false],
    ];
}

/**
 * deleteUser removes the row even when the notification email fails to send.
 */
it('test_delete_user', function (): void {
    $user = makeServiceUser();
    $userId = $user->id;

    $this->mail->shouldReceive('sendMailable')->once()->andThrow(new \Exception('SMTP error'));

    $result = $this->service->deleteUser($user, 'admin');

    expect($result)->toBeTrue()
        ->and(User::find($userId))->toBeNull();
});

/**
 * getUserById returns the user for a valid id string, or null when missing.
 */
it('test_get_user_by_id', function (bool $exists): void {
    if ($exists) {
        $user = makeServiceUser();
        expect($this->service->getUserById((string) $user->id))->not->toBeNull();
    } else {
        expect($this->service->getUserById('9999999'))->toBeNull();
    }
})->with(dataProviderForTestGetUserById());

/**
 * Provides existence flags for testForGetUserById.
 */
function dataProviderForTestGetUserById(): array
{
    return [
        'existing user returned' => [true],
        'unknown id returns null' => [false],
    ];
}

/**
 * blockUser sets is_blocked, persists, and sends the blocked notification.
 */
it('test_block_user', function (): void {
    $user = makeServiceUser();

    $this->mail->shouldReceive('sendMailable')->once();

    $result = $this->service->blockUser($user);

    expect($result)->toBeTrue()
        ->and(User::find($user->id)->is_blocked)->toBeTrue();
});

/**
 * unblockUser clears is_blocked, persists, and sends the unblocked notification.
 */
it('test_unblock_user', function (): void {
    $user = makeServiceUser(blocked: true);

    $this->mail->shouldReceive('sendMailable')->once();

    $result = $this->service->unblockUser($user);

    expect($result)->toBeTrue()
        ->and(User::find($user->id)->is_blocked)->toBeFalse();
});

/**
 * isUserBlocked reflects the user's current blocked flag.
 */
it('test_is_user_blocked', function (bool $blocked): void {
    $user = makeServiceUser(blocked: $blocked);

    expect($this->service->isUserBlocked($user))->toBe($blocked);
})->with(dataProviderForTestIsUserBlocked());

/**
 * Provides blocked state flags for testForIsUserBlocked.
 */
function dataProviderForTestIsUserBlocked(): array
{
    return [
        'blocked user returns true' => [true],
        'unblocked user returns false' => [false],
    ];
}

function makeServiceUser(bool $blocked = false): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
        'is_blocked' => $blocked,
    ]);
}
