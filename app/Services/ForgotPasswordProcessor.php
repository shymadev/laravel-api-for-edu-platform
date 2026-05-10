<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ForgotPasswordDTO;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Password;

/**
 * Service class responsible for processing forgot password requests.
 */
#[Singleton]
final class ForgotPasswordProcessor
{
    /**
     * @param ForgotPasswordDTO $dto
     *
     * @return string a {@see \Illuminate\Contracts\Auth\PasswordBroker} status string
     */
    public function process(ForgotPasswordDTO $dto): string
    {
        return Password::sendResetLink($dto->toArray());
    }
}
