<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Actions\Auth\HandleGoogleCallback;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\DTO\Auth\LoginDTO;
use App\DTO\User\CreateUserDTO;
use App\Enums\Role;
use App\Mail\WelcomeMail;
use App\Models\User\User;
use App\Services\Contracts\Auth\AuthServiceInterface;
use App\Services\Contracts\Auth\TokenGeneratorInterface;
use App\Services\Contracts\Mail\MailServiceInterface;
use App\Services\Contracts\User\UserProfileServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\TransientToken;
use Laravel\Socialite\Two\User as SocialiteUser;

class AuthService implements AuthServiceInterface
{
    /**
     * Constructs Auth service object.
     *
     * @param RegisterUser                $registerUserAction
     * @param LoginUser                   $loginUserAction
     * @param UserProfileServiceInterface $userProfileService
     * @param HandleGoogleCallback        $handleGoogleCallback
     * @param TokenGeneratorInterface     $tokenGenerator
     */
    public function __construct(
        protected readonly RegisterUser $registerUserAction,
        protected readonly LoginUser $loginUserAction,
        protected readonly UserProfileServiceInterface $userProfileService,
        protected readonly HandleGoogleCallback $handleGoogleCallback,
        protected readonly TokenGeneratorInterface $tokenGenerator,
        protected readonly MailServiceInterface $mailService
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function register(CreateUserDTO $dto): array
    {
        $user = $this->registerUserAction->execute($dto);
        $emptyProfile = $this->userProfileService->createEmptyProfile();
        $this->userProfileService->assignProfileToUser($user, $emptyProfile);

        try {
            if (! empty($user->email)) {
                $this->mailService->sendMailable($user->email, new WelcomeMail($user));
            }
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to send welcome email', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }

        Auth::login($user, true);

        return [
            'user' => $user,
            'token' => $user->createToken('access_token')->plainTextToken,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function login(LoginDTO $dto): ?array
    {
        if (! $this->loginUserAction->execute(['email' => $dto->email, 'password' => $dto->password])) {
            return null;
        }

        $user = User::where('email', $dto->email)->first();

        return [
            'user' => $user,
            'token' => $this->tokenGenerator->generateAccessToken($user, Role::from($user->role->role_name)),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token && ! ($token instanceof TransientToken)) {
            $token->delete();
        }

        $user->tokens()->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function handleGoogleCallback(SocialiteUser $googleUser): array
    {
        $user = $this->handleGoogleCallback->execute($googleUser);
        $emptyProfile = $this->userProfileService->createEmptyProfile();
        $this->userProfileService->assignProfileToUser($user, $emptyProfile);

        Auth::login($user, true);

        return [
            'user' => $user,
            'token' => $user->createToken('access_token')->plainTextToken,
        ];
    }
}
