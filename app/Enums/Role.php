<?php

namespace App\Enums;

enum Role: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';

    public function getValue(string $role): Role
    {
        return match ($role) {
            'user' => Role::USER,
            'admin' => Role::ADMIN,
            'moderator' => Role::MODERATOR,
            default => throw new \InvalidArgumentException("Invalid role: $role"),
        };
    }
}
