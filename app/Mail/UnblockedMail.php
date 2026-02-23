<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnblockedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Ваш аккаунт разблокирован')
            ->view('emails.unblocked', ['user' => $this->user]);
    }
}
