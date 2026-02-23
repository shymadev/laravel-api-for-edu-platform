<?php

declare(strict_types=1);

namespace App\Services\Contracts\User;

use App\DTO\Profile\UpdateProfileDTO;
use App\Models\User\Profile;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;

interface UserProfileServiceInterface
{
    /**
     * Get the profile for the given user.
     *
     * @param User $user
     *
     * @return Profile
     */
    public function getUserProfile(User $user): Profile;

    /**
     * Get profile by its ID.
     *
     * @param int $id
     *
     * @return Profile|null
     */
    public function getProfileById(int $id): ?Profile;

    /**
     * Create an empty profile.
     *
     * @return Profile
     */
    public function createEmptyProfile(): Profile;

    /**
     * Assign the given profile to the user.
     *
     * @param User    $user
     * @param Profile $profileToAssign
     *
     * @return void
     */
    public function assignProfileToUser(User $user, Profile $profileToAssign): void;

    /**
     * Update the given profile with the provided data.
     *
     * @param Profile          $profile
     * @param UpdateProfileDTO $updateProfileDTO
     *
     * @return void
     */
    public function updateProfile(Profile $profile, UpdateProfileDTO $updateProfileDTO): void;

    /**
     * Update the avatar of the given profile.
     *
     * @param UploadedFile $uploadedFile
     * @param Profile      $profile
     *
     * @return void
     */
    public function updateProfileAvatar(UploadedFile $uploadedFile, Profile $profile): void;
}
