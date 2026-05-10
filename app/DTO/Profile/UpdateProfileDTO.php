<?php

declare(strict_types=1);

namespace App\DTO\Profile;

/**
 * Data Transfer Object for updating a user profile.
 */
readonly class UpdateProfileDTO
{
    /**
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $bio
     */
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $bio,
    ) {
    }
}
