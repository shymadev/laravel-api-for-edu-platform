<?php

declare(strict_types=1);

namespace App\Services\Strategy\ChangePassword;

use App\DTO\ChangePasswordDTO;
use Illuminate\Support\Facades\Hash;

/**
 * Strategy for handling password changes for users authenticated via standard credentials.
 */
final class StandardChangePasswordStrategy implements ChangePasswordStrategy
{
    /**
     * {@inheritDoc}
     *
     * @param ChangePasswordDTO $dto
     *
     * @return boolean
     */
    public function supports(ChangePasswordDTO $dto): bool
    {
        if ($dto->user->google_id !== null && !$dto->user->hasLocalPassword()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param ChangePasswordDTO $dto
     *
     * @return boolean
     */
    public function handle(ChangePasswordDTO $dto): bool
    {
        if (!Hash::check($dto->oldPassword, $dto->user->password_hash)) {
            return false;
        }

        if (Hash::check($dto->newPassword, $dto->user->password_hash)) {
            throw new \InvalidArgumentException('New password cannot be the same as the old password.');
        }

        $dto->user->password_hash = Hash::make($dto->newPassword);

        return $dto->user->save();
    }
}
