<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $subjectLine;
    public string $viewName;
    public array $data;

    public function __construct(string $subjectLine, string $viewName, array $data = [])
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->data = $data;
    }

    public function build()
    {
        $subject = $this->subjectLine ?: 'Уведомление от Tallksy';

        return $this->subject($subject)
            ->view($this->viewName, $this->data);
    }
}
