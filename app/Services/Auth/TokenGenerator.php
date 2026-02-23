<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Role;
use App\Models\User\User;
use App\Services\Contracts\Auth\TokenGeneratorInterface;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateAccessToken(User $user, Role $role): string
    {
        return $user->createToken('access_token', [$role->value])->plainTextToken;
    }
}
