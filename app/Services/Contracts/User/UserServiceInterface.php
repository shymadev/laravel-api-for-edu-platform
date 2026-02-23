<?php

declare(strict_types=1);

namespace App\Services\Contracts\User;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Models\User\User;

interface UserServiceInterface
{
    /**
     * Create a new user.
     *
     * @param CreateUserDTO $createUserDTO
     *
     * @return User
     */
    public function createUser(CreateUserDTO $createUserDTO): User;

    /**
     * Update an existing user.
     *
     * @param UpdateUserDTO $updateUserDTO
     * @param User          $user
     *
     * @return User
     */
    public function updateUser(User $user, UpdateUserDTO $updateUserDTO): User;

    /**
     * Delete a user by ID.
     *
     * @param User        $user
     * @param string|null $deletedBy Optional identifier of the admin who deleted the user
     *
     * @return bool|null
     */
    public function deleteUser(User $user, ?string $deletedBy = null): bool | null;

    /**
     * Retrieve a user by ID.
     *
     * @param string $userId
     *
     * @return User|null
     */
    public function getUserById(string $userId): ?User;

    /**
     * Block a user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function blockUser(User $user): bool;

    /**
     * Unblock a user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function unblockUser(User $user): bool;

    /**
     * Check if a user is blocked.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isUserBlocked(User $user): bool;
}
