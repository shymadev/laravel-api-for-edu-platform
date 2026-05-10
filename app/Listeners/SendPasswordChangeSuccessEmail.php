<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserPasswordChanged;
use App\Mail\PasswordChanged;
use App\Services\MailService;

/**
 * Sends a password-changed confirmation email when a user updates their password.
 */
class SendPasswordChangeSuccessEmail
{
    /**
     * Construct the event listener.
     *
     * @param MailService $mailService
     */
    public function __construct(protected readonly MailService $mailService)
    {
    }

    /**
     * Handle the event.
     *
     * @param UserPasswordChanged $event
     *
     * @return void
     */
    public function handle(UserPasswordChanged $event): void
    {
        /** @var \App\Models\User\User $user */
        $user = $event->user;

        $this->mailService->sendMailable(
            $user->email,
            new PasswordChanged($user),
        );
    }
}
