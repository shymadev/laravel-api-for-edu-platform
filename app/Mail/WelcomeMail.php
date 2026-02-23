<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;
    public ?string $actionUrl;

    public function __construct($user, ?string $actionUrl = null)
    {
        $this->user = $user;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject('Добро пожаловать в Tallksy')
            ->view('emails.welcome', ['user' => $this->user, 'action_url' => $this->actionUrl]);
    }
}
