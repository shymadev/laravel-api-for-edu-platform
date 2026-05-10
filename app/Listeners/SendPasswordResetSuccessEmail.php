<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Mail\PasswordResetSuccessEmail;
use App\Services\MailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sends a password-reset success email after a user completes the reset flow.
 */
class SendPasswordResetSuccessEmail implements ShouldQueue
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
     * Handle the PasswordReset event by sending a confirmation email.
     *
     * @param PasswordReset $event
     *
     * @return void
     */
    public function handle(PasswordReset $event): void
    {
        /** @var \App\Models\User\User $user */
        $user = $event->user;

        $this->mailService->sendMailable(
            $user->email,
            new PasswordResetSuccessEmail($user),
        );
    }
}
