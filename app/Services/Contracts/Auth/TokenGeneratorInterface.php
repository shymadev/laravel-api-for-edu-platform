<?php

declare(strict_types=1);

namespace App\Services\Contracts\Auth;

use App\Enums\Role;
use App\Models\User\User;

interface TokenGeneratorInterface
{
    /**
     * Generate an access token for the given user and role.
     *
     * @param User $user
     * @param Role $role
     *
     * @return string
     */
    public function generateAccessToken(User $user, Role $role): string;
}
