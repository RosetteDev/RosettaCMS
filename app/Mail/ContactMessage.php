<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $messageSubject,
        public string $messageBody,
        public string $siteName,
        public array $customFields = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contact form: ' . $this->messageSubject,
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-message',
        );
    }
}
