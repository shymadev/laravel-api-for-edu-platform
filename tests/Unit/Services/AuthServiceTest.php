<?php

declare(strict_types=1);

use App\Services\Auth\AuthService;
use App\Models\User\User;
use App\DTO\Auth\LoginDTO;

beforeEach(function () {
    $this->registerUserAction = Mockery::mock(\App\Actions\Auth\RegisterUser::class);
    $this->loginUserAction = Mockery::mock(\App\Actions\Auth\LoginUser::class);
    $this->userProfileService = Mockery::mock(\App\Services\Contracts\User\UserProfileServiceInterface::class);
    $this->handleGoogleCallback = Mockery::mock(\App\Actions\Auth\HandleGoogleCallback::class);
    $this->tokenGenerator = Mockery::mock(\App\Services\Contracts\Auth\TokenGeneratorInterface::class);
    $this->mailService = Mockery::mock(\App\Services\Contracts\Mail\MailServiceInterface::class);

    $this->service = new AuthService(
        $this->registerUserAction,
        $this->loginUserAction,
        $this->userProfileService,
        $this->handleGoogleCallback,
        $this->tokenGenerator,
        $this->mailService
    );
});

afterEach(function () {
    Mockery::close();
});

test('login returns null for invalid credentials', function () {
    $dto = new LoginDTO(
        email: 'test@example.com',
        password: 'wrongpassword'
    );

    $this->loginUserAction->shouldReceive('execute')
        ->with(['email' => $dto->email, 'password' => $dto->password])
        ->once()
        ->andReturn(false);

    $result = $this->service->login($dto);

    expect($result)->toBeNull();
});

test('logout deletes user tokens', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldAllowMockingProtectedMethods();

    $token = Mockery::mock(\Laravel\Sanctum\PersonalAccessToken::class);
    $token->shouldReceive('delete')->once();

    $user->shouldReceive('currentAccessToken')->once()->andReturn($token);

    $tokensRelation = Mockery::mock();
    $tokensRelation->shouldReceive('delete')->once();
    $user->shouldReceive('tokens')->once()->andReturn($tokensRelation);

    $this->service->logout($user);

    expect(true)->toBeTrue(); // Passes if no exception
});
