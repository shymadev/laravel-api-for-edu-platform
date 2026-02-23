<?php

declare(strict_types=1);

namespace App\Services\Contracts\Mail;

use Illuminate\Contracts\Mail\Mailable;

interface MailServiceInterface
{
    /**
     * Send a simple templated email.
     *
     * @param string $to       recipient email
     * @param string $subject  email subject
     * @param string $template view name (blade) under resources/views/emails
     * @param array  $data     data to pass to the view
     *
     * @return void
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = []): void;

    /**
     * Send a mailable instance.
     *
     * @param string   $to       recipient email
     * @param Mailable $mailable
     *
     * @return void
     */
    public function sendMailable(string $to, Mailable $mailable): void;
}
