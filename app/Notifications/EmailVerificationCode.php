<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCode extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public string $code) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        return (new MailMessage)
            ->subject(__('auth.verification_email_subject'))
            ->greeting(__('auth.verification_email_greeting', ['name' => $notifiable->name]))
            ->line(__('auth.verification_email_intro'))
            ->line($this->code)
            ->line(__('auth.verification_email_expiry'))
            ->line(__('auth.verification_email_ignore'));
    }
}
