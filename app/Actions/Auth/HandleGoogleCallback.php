<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleGoogleCallback
{
    public function execute(SocialiteUser $googleUser): User
    {
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user === null) {
            $user = User::create([
                'username' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password_hash' => Hash::make(uniqid()),
                'google_id' => $googleUser->getId(),
            ]);

            return $user;
        }

        if ($user->google_id === null) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        return $user;
    }
}
