<?php

declare(strict_types=1);

namespace App\Services\User;

use App\DTO\Profile\UpdateProfileDTO;
use App\Models\User\Profile;
use App\Models\User\User;
use App\Services\Contracts\Storage\ImageStorageInterface;
use App\Services\Contracts\User\UserProfileServiceInterface;
use Illuminate\Http\UploadedFile;

class UserProfileService implements UserProfileServiceInterface
{
    /**
     * Constructs a new user profile service instance.
     *
     * @param ImageStorageInterface $imageStorage
     */
    public function __construct(
        protected readonly ImageStorageInterface $imageStorage,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile(User $user): Profile
    {
        $profileId = $user->profile_id;

        return Profile::find($profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function createEmptyProfile(): Profile
    {
        $profile = new Profile();
        $profile->save();

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function assignProfileToUser(User $user, Profile $profileToAssign): void
    {
        $user->profile_id = $profileToAssign->id;
        $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileById(int $id): ?Profile
    {
        return Profile::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(Profile $profile, UpdateProfileDTO $updateProfileDTO): void
    {
        $profile->first_name = $updateProfileDTO->firstName;
        $profile->last_name = $updateProfileDTO->lastName;
        $profile->bio = $updateProfileDTO->bio;
        $profile->save();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfileAvatar(UploadedFile $uploadedFile, Profile $profile): void
    {
        if ($profile->avatar_url && $this->imageStorage->exists($profile->avatar_url)) {
            $this->imageStorage->delete($profile->avatar_url);
        }

        $imagePath = $this->imageStorage->upload($uploadedFile, 'user_avatars');
        $profile->avatar_url = $imagePath;
        $profile->save();
    }
}
