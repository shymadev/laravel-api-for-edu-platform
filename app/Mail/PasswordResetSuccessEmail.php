<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable notifying the user that their password was reset successfully (post reset flow).
 *
 * Uses Laravel 9+ envelope/content API; the view receives the authenticated user and frontend home URL.
 */
class PasswordResetSuccessEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param Authenticatable $user
     */
    public function __construct(public readonly Authenticatable $user)
    {
    }

    /**
     * Define subject and metadata for the outgoing message.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Пароль в аккаунте Tallksy изменён',
        );
    }

    /**
     * Define the Blade view and data passed to the template.
     *
     * @return Content
     */
    public function content(): Content
    {
        $homePageUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return new Content(
            view: 'mail.password-reset-success-email',
            with: [
                'user' => $this->user,
                'homePageUrl' => $homePageUrl,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
