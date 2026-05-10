<?php

declare(strict_types=1);

namespace App\Services\Strategy\ChangePassword;

use App\DTO\ChangePasswordDTO;
use Illuminate\Support\Facades\Hash;

/**
 * Strategy for handling password changes for users authenticated via Google.
 */
final class GoogleChangePasswordStrategy implements ChangePasswordStrategy
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
        return $dto->user->google_id !== null && !$dto->user->hasLocalPassword();
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
        $dto->user->password_hash = Hash::make($dto->newPassword);

        return $dto->user->save();
    }
}
