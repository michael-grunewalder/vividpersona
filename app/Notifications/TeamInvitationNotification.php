<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public TeamInvitation $invitation) {}

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
        $team = $this->invitation->team;
        $inviter = $this->invitation->inviter?->name ?? config('app.name');

        return (new MailMessage)
            ->subject(__('invitations.email_subject', ['team' => $team->name]))
            ->greeting(__('invitations.email_greeting', ['name' => $this->invitation->name]))
            ->line(__('invitations.email_intro', [
                'inviter' => $inviter,
                'team' => $team->name,
                'role' => $this->invitation->role->label(),
            ]))
            ->action(__('invitations.email_action'), route('register', ['invitation' => $this->invitation->token]))
            ->line(__('invitations.email_outro'));
    }
}
