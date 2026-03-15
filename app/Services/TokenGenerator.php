<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Models\User\User;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class TokenGenerator
{
    public function generateAccessToken(User $user, Role $role): string
    {
        return $user->createToken('access_token', [$role->value])->plainTextToken;
    }
}
