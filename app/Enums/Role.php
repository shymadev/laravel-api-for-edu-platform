<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Represents the available user roles in the application.
 */
enum Role: string
{
    /**
     * Resolve a Role enum case from its string identifier.
     *
     * @param string $role
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function getValue(string $role): Role
    {
        return match ($role) {
            'user' => Role::USER,
            'admin' => Role::ADMIN,
            'moderator' => Role::MODERATOR,
            default => throw new \InvalidArgumentException("Invalid role: $role"),
        };
    }
    case USER = 'user';
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
}
