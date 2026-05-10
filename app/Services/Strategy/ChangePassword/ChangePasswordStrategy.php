<?php

declare(strict_types=1);

namespace App\Services\Strategy\ChangePassword;

use App\DTO\ChangePasswordDTO;

/**
 * Interface defining the contract for change password strategies.
 */
interface ChangePasswordStrategy
{
    /**
     * Determines if the strategy supports handling the given ChangePasswordDTO.
     *
     * @param ChangePasswordDTO $dto
     *
     * @return boolean
     */
    public function supports(ChangePasswordDTO $dto): bool;

    /**
     * Handles the password change process for the given ChangePasswordDTO.
     *
     * @param ChangePasswordDTO $dto
     *
     * @return boolean
     */
    public function handle(ChangePasswordDTO $dto): bool;
}
