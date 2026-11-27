<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $formName,
        public array $fields,
        public array $values,
        public string $siteName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->formName . '] New submission',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.form-submission',
        );
    }
}
