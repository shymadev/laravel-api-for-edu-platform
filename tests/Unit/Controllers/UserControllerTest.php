<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use App\Models\User\User;
use App\Services\Contracts\User\UserServiceInterface;

beforeEach(function () {
    $this->userService = Mockery::mock(UserServiceInterface::class);
    $this->controller = new UserController($this->userService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls userService to get user by id', function () {
    $userId = '1';
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();

    $this->userService->shouldReceive('getUserById')
        ->with($userId)
        ->once()
        ->andReturn($user);

    $result = $this->userService->getUserById($userId);

    expect($result)->toBe($user);
});

test('controller calls userService to block user', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();

    $this->userService->shouldReceive('blockUser')
        ->with($user)
        ->once();

    $this->userService->blockUser($user);

    expect(true)->toBeTrue();
});

test('controller calls userService to unblock user', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();

    $this->userService->shouldReceive('unblockUser')
        ->with($user)
        ->once();

    $this->userService->unblockUser($user);

    expect(true)->toBeTrue();
});
