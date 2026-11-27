<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $siteName,
        public int $hours,
        public \Illuminate\Support\Collection $posts,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->siteName} — {$this->hours}h digest",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.post-digest',
        );
    }
}
