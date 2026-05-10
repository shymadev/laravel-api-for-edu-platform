<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Handle Google callback.
 */
class HandleGoogleCallback
{
    /**
     * Find or create a local user from the Google OAuth payload.
     *
     * @param SocialiteUser $googleUser
     *
     * @return User
     */
    public function execute(SocialiteUser $googleUser): User
    {
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user === null) {
            return User::create([
                'username' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password_hash' => '',
                'google_id' => $googleUser->getId(),
            ]);
        }

        if ($user->google_id === null) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        return $user;
    }
}
