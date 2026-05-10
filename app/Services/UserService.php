<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Mail\AccountDeletedMail;
use App\Mail\BlockedMail;
use App\Mail\UnblockedMail;
use App\Models\User\User;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Service class for managing users.
 */
#[Singleton]
class UserService
{
    /**
     * Constructs a new user service instance.
     *
     * @param MailService $mailService
     */
    public function __construct(
        protected readonly MailService $mailService,
    ) {
    }

    /**
     * Create a new user.
     *
     * @param CreateUserDTO $createUserDTO
     *
     * @return User
     */
    public function createUser(CreateUserDTO $createUserDTO): User
    {
        return User::create([
            'username' => $createUserDTO->username,
            'email' => $createUserDTO->email,
            'password_hash' => Hash::make($createUserDTO->password),
            'role_id' => $createUserDTO->roleId !== null ? $createUserDTO->roleId : 1,
        ]);
    }

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param UpdateUserDTO $updateUserDTO
     *
     * @return User
     */
    public function updateUser(User $user, UpdateUserDTO $updateUserDTO): User
    {
        $shouldRevokeTokens = false;

        foreach ($updateUserDTO->toArray() as $key => $value) {
            if ($value !== null) {
                if ($key === 'password') {
                    $user->password_hash = Hash::make($value);
                    $shouldRevokeTokens = true;
                } elseif ($key === 'roleId') {
                    $user->role_id = $value;
                } elseif ($key === 'email') {
                    $user->email = $value;
                    $shouldRevokeTokens = true;
                } else {
                    /** @phpstan-ignore property.dynamicName */
                    $user->$key = $value;
                }
            }
        }

        $user->save();

        if ($shouldRevokeTokens) {
            $user->tokens()->delete();
        }

        return $user;
    }

    /**
     * Delete a user by ID.
     *
     * @param User $user
     * @param ?string $deletedBy
     *
     * @return ?bool
     */
    public function deleteUser(User $user, ?string $deletedBy = null): ?bool
    {
        try {
            if ($user->email !== '') {
                $this->mailService->sendMailable($user->email, new AccountDeletedMail($user, $deletedBy));
            }
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to send account deleted email', [
                'user_id' => $user->id,
                'deleted_by' => $deletedBy,
                'exception' => $e->getMessage(),
            ]);
        }

        return $user->delete();
    }

    /**
     * Retrieve a user by ID.
     *
     * @param string $userId
     *
     * @return ?User
     */
    public function getUserById(string $userId): ?User
    {
        return User::query()->where('id', $userId)->firstOr(function () {
            return null;
        });
    }

    /**
     * Block a user.
     *
     * @param User $user
     *
     * @return boolean
     */
    public function blockUser(User $user): bool
    {
        $user->is_blocked = true;
        $user->save();
        $user->tokens()->delete();

        try {
            if ($user->email !== '') {
                $this->mailService->sendMailable($user->email, new BlockedMail($user));
            }
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to send blocked email', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return $user->is_blocked;
    }

    /**
     * Unblock a user.
     *
     * @param User $user
     *
     * @return boolean
     */
    public function unblockUser(User $user): bool
    {
        $user->is_blocked = false;
        $user->save();

        try {
            if ($user->email !== '') {
                $this->mailService->sendMailable($user->email, new UnblockedMail($user));
            }
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to send unblocked email', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return !$user->is_blocked;
    }

    /**
     * Check if a user is blocked.
     *
     * @param User $user
     *
     * @return boolean
     */
    public function isUserBlocked(User $user): bool
    {
        return $user->is_blocked ?? false;
    }
}
