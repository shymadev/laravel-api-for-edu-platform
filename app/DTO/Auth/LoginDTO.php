<?php

declare(strict_types=1);

namespace App\DTO\Auth;

/**
 * Data Transfer Object for user login credentials.
 */
readonly class LoginDTO
{
    /**
     * @param string $login
     * @param string $password
     */
    public function __construct(
        public string $login,
        public string $password,
    ) {
    }

    /**
     * Creates a LoginDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            login: $data['login'],
            password: $data['password'],
        );
    }
}
