<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to a user when their account has been deleted.
 */
class AccountDeletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    public ?string $deletedBy;

    /**
     * @param mixed $user
     * @param string|null $deletedBy
     */
    public function __construct(mixed $user, ?string $deletedBy = null)
    {
        $this->user = $user;
        $this->deletedBy = $deletedBy;
    }

    /**
     * Build the message.
     *
     * @return static
     */
    public function build()
    {
        return $this->subject('Ваш аккаунт удалён')
            ->view('mail.deleted', ['user' => $this->user, 'deleted_by' => $this->deletedBy]);
    }
}
