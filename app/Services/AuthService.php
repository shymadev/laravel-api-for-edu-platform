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
use App\Services\Contracts\Mail\MailServiceInterface;
use App\Services\Contracts\User\UserProfileServiceInterface;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\TransientToken;
use Laravel\Socialite\Two\User as SocialiteUser;

#[Singleton]
readonly class AuthService
{
    /**
     * Constructs Auth service object.
     *
     * @param RegisterUser                $registerUserAction
     * @param LoginUser                   $loginUserAction
     * @param UserProfileServiceInterface $userProfileService
     * @param HandleGoogleCallback        $handleGoogleCallback
     * @param TokenGenerator     $tokenGenerator
     * @param MailServiceInterface        $mailService
     */
    public function __construct(
        protected RegisterUser                $registerUserAction,
        protected LoginUser                   $loginUserAction,
        protected UserProfileServiceInterface $userProfileService,
        protected HandleGoogleCallback        $handleGoogleCallback,
        protected TokenGenerator              $tokenGenerator,
        protected MailServiceInterface        $mailService
    ) {
    }

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
     * Logs in a user using either email or username.
     *
     * @param LoginDTO $dto
     *    The data transfer object containing login credentials.
     *
     * @return array|null
     *    Returns an array with user and token if login is successful, or null if it fails.
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

        if (!User::where('id', $user->id)) {
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
