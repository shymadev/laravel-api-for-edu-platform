<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent when a user changes their password while logged in (not the forgot-password flow).
 */
class PasswordChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Authenticatable $user
     */
    public function __construct(
        public readonly Authenticatable $user,
    ) {
    }

    /**
     * Get the message envelope.
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
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        $homePageUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return new Content(
            view: 'mail.password-changed',
            with: [
                'user' => $this->user,
                'homePageUrl' => $homePageUrl,
                'changedAt' => now()->timezone(config('app.timezone'))->format('d.m.Y H:i'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
