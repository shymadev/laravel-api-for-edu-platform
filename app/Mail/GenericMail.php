<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic mailable for sending arbitrary view-based emails with a custom subject.
 */
class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $subjectLine;

    public string $viewName;

    /**
     * @var array<string, mixed>
     */
    public array $data;

    /**
     * @param string $subjectLine
     * @param string $viewName
     * @param array<string, mixed> $data
     */
    public function __construct(string $subjectLine, string $viewName, array $data = [])
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return static
     */
    public function build()
    {
        $subject = $this->subjectLine !== '' ? $this->subjectLine : 'Уведомление от Tallksy';

        return $this->subject($subject)
            ->view($this->viewName, $this->data);
    }
}
