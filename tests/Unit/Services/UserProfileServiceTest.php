<?php

declare(strict_types=1);

/**
 * Unit tests for UserProfileService.
 */

use App\DTO\Profile\UpdateProfileDTO;
use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Storage\ImageStorageService;
use App\Services\UserProfileService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->imageStorage = Mockery::mock(ImageStorageService::class);
    $this->service = new UserProfileService($this->imageStorage);

    Role::upsert(
        [['id' => Role::USER_ROLE_ID, 'role_name' => 'user']],
        ['id'],
    );

    $this->profile = Profile::create(['first_name' => 'John', 'last_name' => 'Doe', 'bio' => null]);

    $this->user = User::create([
        'username' => 'profileuser',
        'email' => 'profile@example.com',
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
        'profile_id' => $this->profile->id,
    ]);
});

/**
 * getUserProfile returns the profile referenced by the user's profile_id.
 */
it('test_get_user_profile', function (): void {
    $result = $this->service->getUserProfile($this->user);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($this->profile->id);
});

/**
 * createEmptyProfile persists a new profile with optional fields left null.
 */
it('test_create_empty_profile', function (): void {
    $profile = $this->service->createEmptyProfile();

    expect($profile->id)->not->toBeNull()
        ->and(Profile::find($profile->id))->not->toBeNull();
});

/**
 * assignProfileToUser updates profile_id on the user row and saves it.
 */
it('test_assign_profile_to_user', function (): void {
    $newProfile = Profile::create();
    $this->service->assignProfileToUser($this->user, $newProfile);

    expect(User::find($this->user->id)->profile_id)->toBe($newProfile->id);
});

/**
 * getProfileById returns the profile or null when the id is unknown.
 */
it('test_get_profile_by_id', function (bool $exists): void {
    if ($exists) {
        expect($this->service->getProfileById($this->profile->id))->not->toBeNull();
    } else {
        expect($this->service->getProfileById(PHP_INT_MAX))->toBeNull();
    }
})->with(dataProviderForTestGetProfileById());

/**
 * Provides existence flags for testForGetProfileById.
 */
function dataProviderForTestGetProfileById(): array
{
    return [
        'existing profile returned' => [true],
        'unknown id returns null' => [false],
    ];
}

/**
 * updateProfile saves first_name, last_name, and bio from the DTO.
 */
it('test_update_profile', function (): void {
    $dto = new UpdateProfileDTO(firstName: 'Jane', lastName: 'Smith', bio: 'A bio');

    $this->service->updateProfile($this->profile, $dto);

    $fresh = Profile::find($this->profile->id);
    expect($fresh->first_name)->toBe('Jane')
        ->and($fresh->last_name)->toBe('Smith')
        ->and($fresh->bio)->toBe('A bio');
});

/**
 * updateProfileAvatar deletes the old file when present, then uploads the new one.
 */
it('test_update_profile_avatar', function (bool $hasExistingAvatar): void {
    $file = UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg');

    if ($hasExistingAvatar) {
        $this->profile->avatar_url = 'https://storage.example.com/old_avatar.jpg';
        $this->profile->save();

        $this->imageStorage->shouldReceive('exists')
            ->once()
            ->with('https://storage.example.com/old_avatar.jpg')
            ->andReturn(true);

        $this->imageStorage->shouldReceive('delete')
            ->once()
            ->with('https://storage.example.com/old_avatar.jpg');
    } else {
        $this->imageStorage->shouldNotReceive('delete');
    }

    $this->imageStorage->shouldReceive('upload')
        ->once()
        ->with($file, 'user_avatars')
        ->andReturn('https://storage.example.com/new_avatar.jpg');

    $this->service->updateProfileAvatar($file, $this->profile);

    expect(Profile::find($this->profile->id)->avatar_url)->toBe('https://storage.example.com/new_avatar.jpg');
})->with(dataProviderForTestUpdateProfileAvatar());

/**
 * Provides existing avatar flags for testForUpdateProfileAvatar.
 */
function dataProviderForTestUpdateProfileAvatar(): array
{
    return [
        'no existing avatar → no delete call' => [false],
        'existing avatar → old one deleted first' => [true],
    ];
}
