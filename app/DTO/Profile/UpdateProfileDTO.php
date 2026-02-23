<?php

declare (strict_types=1);

namespace App\DTO\Profile;

class UpdateProfileDTO
{
    public function __construct(
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $bio,
    ) {
    }

}
