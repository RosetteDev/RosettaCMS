<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    public function __construct(
        public string $token,
        public ?string $siteName = null,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteName = $this->siteName ?? $this->defaultSiteName();

        $resetUrl = url('/reset-password/' . $this->token . '?email=' . urlencode($notifiable->email));

        return (new MailMessage())
            ->subject("Reset your {$siteName} password")
            ->greeting('Hi ' . ($notifiable->name ?? 'there') . ',')
            ->line("We received a request to reset your {$siteName} password.")
            ->action('Reset password', $resetUrl)
            ->line('This link expires in 60 minutes. If you didn\'t request this, you can ignore this email.')
            ->salutation('Thanks, ' . $siteName);
    }

    protected function defaultSiteName(): string
    {
        try {
            return SiteSetting::forSite()->site_title ?? 'Rosetta CMS';
        } catch (\Throwable $e) {
            return 'Rosetta CMS';
        }
    }
}
