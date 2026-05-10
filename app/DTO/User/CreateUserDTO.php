<?php

declare(strict_types=1);

namespace App\DTO\User;

/**
 * Data Transfer Object for creating a new user.
 */
readonly class CreateUserDTO
{
    /**
     * Constructs a new CreateUserDTO instance.
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param int|null $roleId
     */
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public ?int $roleId,
    ) {
    }

    /**
     * Creates a CreateUserDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
            roleId: isset($data['roleId']) ? (int) $data['roleId'] : null,
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'roleId' => $this->roleId,
        ];
    }
}
