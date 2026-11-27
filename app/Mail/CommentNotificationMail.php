<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $commentAuthor,
        public string $postTitle,
        public string $postUrl,
        public string $commentBody,
        public string $siteName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New comment on {$this->postTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.comment-notification',
        );
    }
}
