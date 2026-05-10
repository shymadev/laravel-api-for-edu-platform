<?php

declare(strict_types=1);

namespace App\DTO\User;

/**
 * Data Transfer Object for updating a user.
 */
readonly class UpdateUserDTO
{
    /**
     * Constructs a new UpdateUserDTO instance.
     *
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @param int|null $roleId
     */
    public function __construct(
        public ?string $username,
        public ?string $email,
        public ?string $password,
        public ?int    $roleId,
    ) {
    }

    /**
     * Creates an UpdateUserDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
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
