<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\GenericMail;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer as MailFactory;

/**
 * Service for sending emails.
 */
#[Singleton]
class MailService
{
    /**
     * Constructs Mail service object.
     *
     * @param MailFactory $mailer
     */
    public function __construct(protected readonly MailFactory $mailer)
    {
    }

    /**
     * Send a template email.
     *
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $data
     *
     * @return void
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = []): void
    {
        $mailable = new GenericMail($subject, $template, $data);
        $this->mailer->to($to)->queue($mailable);
    }

    /**
     * Send a mailable email.
     *
     * @param string $to
     * @param Mailable $mailable
     *
     * @return void
     */
    public function sendMailable(string $to, Mailable $mailable): void
    {
        $this->mailer->to($to)->queue($mailable);
    }
}
