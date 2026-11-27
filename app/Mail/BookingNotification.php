<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $bookingName,
        public string $bookingEmail,
        public ?string $bookingPhone,
        public string $service,
        public string $date,
        public ?string $time,
        public ?string $notes,
        public string $siteName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New booking: ' . $this->service,
            replyTo: [new Address($this->bookingEmail, $this->bookingName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.booking-notification',
        );
    }
}
