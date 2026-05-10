<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to a user when their account has been blocked.
 */
class BlockedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    /**
     * @var string|null
     */
    public $reason;

    /**
     * @param mixed $user
     * @param string|null $reason
     */
    public function __construct(mixed $user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return static
     */
    public function build()
    {
        return $this->subject('Ваш аккаунт заблокирован')
            ->view('mail.blocked', ['user' => $this->user, 'reason' => $this->reason]);
    }
}
