<?php

declare(strict_types=1);

namespace App\DTO\Auth;

readonly class LoginDTO
{
    public function __construct(
        public string $login,
        public string $password,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            login: $data['login'],
            password: $data['password'],
        );
    }
}
