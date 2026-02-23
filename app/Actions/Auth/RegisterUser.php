<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\User\CreateUserDTO;
use App\Models\User\User;
use App\Services\Contracts\User\UserServiceInterface;

class RegisterUser
{
    /**
     * Constructs RegisterUser.
     *
     * @param UserServiceInterface $userService
     *                                          The user service
     */
    public function __construct(protected readonly UserServiceInterface $userService)
    {
    }

    public function execute(CreateUserDTO $createUserDTO): User
    {
        return $this->userService->createUser($createUserDTO);
    }
}
