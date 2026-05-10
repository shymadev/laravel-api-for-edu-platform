<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Models\User\User;
use Illuminate\Container\Attributes\Singleton;

/**
 * Generates Sanctum access tokens for authenticated users.
 */
#[Singleton]
class TokenGenerator
{
    /**
     * Generate a plain-text Sanctum access token for the given user and role.
     *
     * @param User $user
     * @param Role $role
     *
     * @return string
     */
    public function generateAccessToken(User $user, Role $role): string
    {
        return $user->createToken('access_token', [$role->value])->plainTextToken;
    }
}
