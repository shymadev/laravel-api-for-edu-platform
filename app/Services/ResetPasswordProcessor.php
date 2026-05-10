<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ResetPasswordDTO;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Service class responsible for processing password reset requests.
 */
#[Singleton]
final class ResetPasswordProcessor
{
    /**
     * Processes the password reset request.
     *
     * @param ResetPasswordDTO $dto
     *
     * @return string
     */
    public function process(ResetPasswordDTO $dto): string
    {
        return Password::reset(
            $dto->toArray(),
            function ($user, string $password): void {
                $user->forceFill([
                    'password_hash' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                Log::channel('db')->info('Password updated after reset token', [
                    'action' => 'password_reset_completed',
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                event(new PasswordReset($user));
            },
        );
    }
}
