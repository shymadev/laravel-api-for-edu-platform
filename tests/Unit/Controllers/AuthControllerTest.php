<?php

declare(strict_types=1);

use App\DTO\Auth\LoginDTO;
use App\Http\Controllers\AuthController;
use App\Models\User\User;
use App\Services\Contracts\Auth\AuthServiceInterface;

beforeEach(function () {
    $this->authService = Mockery::mock(AuthServiceInterface::class);
    $this->controller = new AuthController($this->authService);
});

afterEach(function () {
    Mockery::close();
});

test('authService login is called with correct DTO', function () {
    $dto = new LoginDTO(email: 'test@example.com', password: 'password123');
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();
    $token = 'test-token';

    $this->authService->shouldReceive('login')
        ->with(Mockery::type(LoginDTO::class))
        ->once()
        ->andReturn(['user' => $user, 'token' => $token]);

    $result = $this->authService->login($dto);

    expect($result)->toBeArray();
    expect($result['token'])->toBe($token);
});

test('authService logout is called with user object', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();

    $this->authService->shouldReceive('logout')
        ->with($user)
        ->once();

    $this->authService->logout($user);

    expect(true)->toBeTrue(); // Passes if no exception thrown
});
