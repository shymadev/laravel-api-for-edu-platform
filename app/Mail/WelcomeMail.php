<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to a user upon successful registration.
 */
class WelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var mixed
     */
    public $user;

    public ?string $actionUrl;

    /**
     * @param mixed $user
     * @param string|null $actionUrl
     */
    public function __construct(mixed $user, ?string $actionUrl = null)
    {
        $this->user = $user;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Build the message.
     *
     * @return static
     */
    public function build()
    {
        return $this->subject('Добро пожаловать в Tallksy')
            ->view('mail.welcome', ['user' => $this->user, 'action_url' => $this->actionUrl]);
    }
}
