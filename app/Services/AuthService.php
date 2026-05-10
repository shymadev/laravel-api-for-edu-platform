<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Auth\HandleGoogleCallback;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\DTO\Auth\LoginDTO;
use App\DTO\User\CreateUserDTO;
use App\Enums\Role;
use App\Mail\WelcomeMail;
use App\Models\User\User;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\TransientToken;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Coordinates user registration, login, logout, and Google OAuth.
 */
#[Singleton]
readonly class AuthService
{
    /**
     * Constructs Auth service object.
     *
     * @param RegisterUser $registerUserAction
     * @param LoginUser $loginUserAction
     * @param UserProfileService $userProfileService
     * @param HandleGoogleCallback $handleGoogleCallback
     * @param TokenGenerator $tokenGenerator
     * @param MailService $mailService
     */
    public function __construct(
        protected RegisterUser $registerUserAction,
        protected LoginUser $loginUserAction,
        protected UserProfileService $userProfileService,
        protected HandleGoogleCallback $handleGoogleCallback,
        protected TokenGenerator $tokenGenerator,
        protected MailService $mailService,
    ) {
    }

    /**
     * Registers a new user, creates an empty profile for them, sends a welcome email, and logs them in.
     *
     * @param CreateUserDTO $dto
     *
     * @return array<string, mixed>
     */
    public function register(CreateUserDTO $dto): array
    {
        $user = $this->registerUserAction->execute($dto);
        $emptyProfile = $this->userProfileService->createEmptyProfile();
        $this->userProfileService->assignProfileToUser($user, $emptyProfile);

        try {
            if ($user->email !== '') {
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
     * Logs in a user using either email or username.
     *
     * @param LoginDTO $dto
     *
     * @return array<string, mixed>|null
     */
    public function login(LoginDTO $dto): ?array
    {
        $isEmail = filter_var($dto->login, FILTER_VALIDATE_EMAIL);

        $credentials = $isEmail
            ? ['email' => $dto->login, 'password' => $dto->password]
            : ['username' => $dto->login, 'password' => $dto->password];

        if (!$this->loginUserAction->execute($credentials)) {
            return null;
        }

        $user = $isEmail
            ? User::where('email', $dto->login)->first()
            : User::where('username', $dto->login)->first();

        return [
            'user' => $user,
            'token' => $this->tokenGenerator->generateAccessToken($user, Role::from($user->role->role_name)),
        ];
    }

    /**
     * Logs out the user by deleting their current access token and all other tokens.
     *
     * @param User $user
     *
     * @return void
     */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        // @phpstan-ignore-next-line
        if ($token && !($token instanceof TransientToken)) {
            $token->delete();
        }

        $user->tokens()->delete();
    }

    /**
     * Handles the Google OAuth callback, creates a user if necessary, and logs them in.
     *
     * @param SocialiteUser $googleUser
     *
     * @return array<string, mixed>
     */
    public function handleGoogleCallback(SocialiteUser $googleUser): array
    {
        $user = $this->handleGoogleCallback->execute($googleUser);

        if ($user->profile()->first() === null) {
            $emptyProfile = $this->userProfileService->createEmptyProfile();
            $this->userProfileService->assignProfileToUser($user, $emptyProfile);
        }

        Auth::login($user, true);

        return [
            'user' => $user,
            'token' => $user->createToken('access_token')->plainTextToken,
        ];
    }
}
