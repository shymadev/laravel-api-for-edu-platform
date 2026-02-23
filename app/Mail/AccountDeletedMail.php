<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;
    public ?string $deletedBy;

    public function __construct($user, ?string $deletedBy = null)
    {
        $this->user = $user;
        $this->deletedBy = $deletedBy;
    }

    public function build()
    {
        return $this->subject('Ваш аккаунт удалён')
            ->view('emails.deleted', ['user' => $this->user, 'deleted_by' => $this->deletedBy]);
    }
}
