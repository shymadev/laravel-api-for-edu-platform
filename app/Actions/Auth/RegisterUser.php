<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\User\CreateUserDTO;
use App\Models\User\User;
use App\Services\UserService;

/**
 * Registers a new user account via the user service.
 */
class RegisterUser
{
    /**
     * @param UserService $userService
     *
     * @return void
     */
    public function __construct(protected readonly UserService $userService)
    {
    }

    /**
     * Execute the register user action and return the newly created user.
     *
     * @param CreateUserDTO $createUserDTO
     *
     * @return User
     */
    public function execute(CreateUserDTO $createUserDTO): User
    {
        return $this->userService->createUser($createUserDTO);
    }
}
