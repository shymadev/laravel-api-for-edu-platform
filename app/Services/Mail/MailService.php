<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Mail\GenericMail;
use App\Services\Contracts\Mail\MailServiceInterface as Contract;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService implements Contract
{
    public function sendTemplate(string $to, string $subject, string $template, array $data = []): void
    {
        $mailable = new GenericMail($subject, $template, $data);
        Mail::to($to)->send($mailable);
    }

    public function sendMailable(string $to, Mailable $mailable): void
    {
        Mail::to($to)->send($mailable);
    }
}
