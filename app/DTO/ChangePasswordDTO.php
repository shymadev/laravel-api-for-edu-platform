<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Traits\ConvertToArrayTrait;
use App\Models\User\User;

/**
 * Data Transfer Object for changing user password.
 */
final readonly class ChangePasswordDTO
{
    use ConvertToArrayTrait;

    /**
     * @param User $user
     * @param string|null $oldPassword
     * @param string $newPassword
     */
    public function __construct(
        public User $user,
        public ?string $oldPassword,
        public string $newPassword,
    ) {
    }

}
