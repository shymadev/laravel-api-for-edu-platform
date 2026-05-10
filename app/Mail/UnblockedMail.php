<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to a user when their account has been unblocked.
 */
class UnblockedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    /**
     * @param mixed $user
     */
    public function __construct(mixed $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return static
     */
    public function build()
    {
        return $this->subject('Ваш аккаунт разблокирован')
            ->view('mail.unblocked', ['user' => $this->user]);
    }
}
