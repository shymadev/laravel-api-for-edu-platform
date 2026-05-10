<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

/**
 * Login user.
 */
class LoginUser
{
    /**
     * Attempt to authenticate a user with the given credentials.
     *
     * @param array<string, string> $credentials
     *
     * @return boolean
     */
    public function execute(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }
}
