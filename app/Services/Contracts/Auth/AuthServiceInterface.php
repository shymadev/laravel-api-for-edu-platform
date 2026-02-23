<?php

declare(strict_types=1);

namespace App\Services\Contracts\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\User\CreateUserDTO;
use App\Models\User\User;
use Laravel\Socialite\Two\User as SocialiteUser;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param CreateUserDTO $dto
     *
     * @return array{user: User, token: string}
     */
    public function register(CreateUserDTO $dto): array;

    /**
     * Login a user.
     *
     * @param LoginDTO $dto
     *
     * @return array{user: User, token: string}|null
     */
    public function login(LoginDTO $dto): ?array;

    /**
     * Logout a user.
     *
     * @param User $user
     *
     * @return void
     */
    public function logout(User $user): void;

    /**
     * Handle Google callback.
     *
     * @param SocialiteUser $googleUser
     *
     * @return array{user: User, token: string}
     */
    public function handleGoogleCallback(SocialiteUser $googleUser): array;
}
