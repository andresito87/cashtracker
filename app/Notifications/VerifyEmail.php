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
            ->subject('Confirma tu cuenta en CashTracker')
            ->greeting('¡Hola!')
            ->line('Gracias por registrarte en CashTracker. Por favor, haz clic en el siguiente enlace para verificar tu cuenta:')
            ->action('Verificar cuenta', $verificationUrl)
            ->line('Si no creaste una cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos, el equipo de CashTracker');
    }
}
