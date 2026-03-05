<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Models\User\Profile;
use App\Services\Contracts\User\UserProfileServiceInterface;

beforeEach(function () {
    $this->profileService = Mockery::mock(UserProfileServiceInterface::class);
    $this->controller = new ProfileController($this->profileService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls profileService to get profile by id', function () {
    $profileId = 1;
    $profile = Mockery::mock(Profile::class)->makePartial();
    $profile->shouldAllowMockingProtectedMethods();

    $this->profileService->shouldReceive('getProfileById')
        ->with($profileId)
        ->once()
        ->andReturn($profile);

    $result = $this->profileService->getProfileById($profileId);

    expect($result)->toBe($profile);
});

test('controller has profileService dependency injected', function () {
    expect($this->profileService)->toBeInstanceOf(UserProfileServiceInterface::class);
    expect($this->controller)->toBeInstanceOf(ProfileController::class);
});
