<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Traits\ConvertToArrayTrait;

/**
 * Data Transfer Object for forgot password functionality.
 */
final readonly class ForgotPasswordDTO
{
    use ConvertToArrayTrait;

    /**
     * Constructs a new instance of the ForgotPasswordDTO class.
     *
     * @param string $email
     */
    public function __construct(
        public string $email,
    ) {
    }
}
