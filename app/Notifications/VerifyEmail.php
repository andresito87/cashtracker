<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     *
     * @noinspection PhpUnusedParameterInspection
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
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject(__('messages.email_verify_subject'))
            ->greeting(__('messages.email_verify_greeting'))
            ->line(__('messages.email_verify_intro'))
            ->action(__('messages.email_verify_action'), $verificationUrl)
            ->line(__('messages.email_verify_disclaimer'))
            ->salutation(__('messages.email_verify_salutation'));
    }
}
