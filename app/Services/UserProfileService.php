<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Profile\UpdateProfileDTO;
use App\Models\User\Profile;
use App\Models\User\User;
use App\Services\Storage\ImageStorageService;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\UploadedFile;

/**
 * Service that handles user profile operations.
 */
#[Singleton]
class UserProfileService
{
    /**
     * @param ImageStorageService $imageStorage
     *
     * @return void
     */
    public function __construct(
        protected readonly ImageStorageService $imageStorage,
    ) {
    }

    /**
     * Get the profile for the given user.
     *
     * @param User $user
     *
     * @return Profile
     */
    public function getUserProfile(User $user): Profile
    {
        $profileId = $user->profile_id;

        return Profile::find($profileId);
    }

    /**
     * Create an empty profile.
     *
     * @return Profile
     */
    public function createEmptyProfile(): Profile
    {
        return Profile::create();
    }

    /**
     * Assign the given profile to the user.
     *
     * @param User $user
     * @param Profile $profileToAssign
     *
     * @return void
     */
    public function assignProfileToUser(User $user, Profile $profileToAssign): void
    {
        $user->profile_id = $profileToAssign->id;
        $user->save();
    }

    /**
     * Get the profile by its ID.
     *
     * @param int $id
     *
     * @return ?Profile
     */
    public function getProfileById(int $id): ?Profile
    {
        return Profile::find($id);
    }

    /**
     * Update the given profile with the provided data.
     *
     * @param Profile $profile
     * @param UpdateProfileDTO $updateProfileDTO
     *
     * @return void
     */
    public function updateProfile(Profile $profile, UpdateProfileDTO $updateProfileDTO): void
    {
        $profile->first_name = $updateProfileDTO->firstName;
        $profile->last_name = $updateProfileDTO->lastName;
        $profile->bio = $updateProfileDTO->bio;
        $profile->save();
    }

    /**
     * Update the avatar of the given profile.
     *
     * @param UploadedFile $uploadedFile
     * @param Profile $profile
     *
     * @return void
     */
    public function updateProfileAvatar(UploadedFile $uploadedFile, Profile $profile): void
    {
        if ($profile->avatar_url !== null && $profile->avatar_url !== '' && $this->imageStorage->exists($profile->avatar_url)) {
            $this->imageStorage->delete($profile->avatar_url);
        }

        $imagePath = $this->imageStorage->upload($uploadedFile, 'user_avatars');
        $profile->avatar_url = $imagePath;
        $profile->save();
    }
}
