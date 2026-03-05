<?php

declare(strict_types=1);

use App\Models\User\User;
use App\Services\Contracts\Mail\MailServiceInterface;
use App\Services\User\UserService;

beforeEach(function () {
    $this->mailService = Mockery::mock(MailServiceInterface::class);
    $this->service = new UserService($this->mailService);
});

afterEach(function () {
    Mockery::close();
});

test('isUserBlocked returns true for blocked user', function () {
    $blockedUser = Mockery::mock(User::class)->makePartial();
    $blockedUser->shouldAllowMockingProtectedMethods();
    $blockedUser->shouldReceive('getAttribute')->with('is_blocked')->andReturn(true);

    $result = $this->service->isUserBlocked($blockedUser);

    expect($result)->toBeTrue();
});

test('isUserBlocked returns false for non-blocked user', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();
    $user->shouldReceive('getAttribute')->with('is_blocked')->andReturn(false);

    $result = $this->service->isUserBlocked($user);

    expect($result)->toBeFalse();
});
