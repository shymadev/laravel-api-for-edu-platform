<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BlockedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;
    public $reason;

    public function __construct($user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Ваш аккаунт заблокирован')
            ->view('emails.blocked', ['user' => $this->user, 'reason' => $this->reason]);
    }
}
