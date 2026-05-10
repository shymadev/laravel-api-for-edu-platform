<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Traits\ConvertToArrayTrait;

/**
 * Data Transfer Object for resetting a user's password.
 */
final readonly class ResetPasswordDTO
{
    use ConvertToArrayTrait;

    /**
     * Constructs a new instance of the ResetPasswordDTO class.
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $passwordConfirmation,
    ) {
    }
}
