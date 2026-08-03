<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

/**
 * Branded password-reset-link notification dispatched through the password
 * broker when a user requests a reset link.
 *
 * The action button is rendered as an inline-styled HTML link so the brand
 * purple (#4C1D95) applies regardless of the configured Markdown mail theme.
 */
class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

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
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            [
                'token' => $this->token,
                'email' => $notifiable->email,
            ]
        );

        $actionLabel = __('messages.passwords.mail.action');

        $button = new HtmlString(
            '<a href="'.e($resetUrl)
            .'" style="display:inline-block;padding:12px 24px;background:#4C1D95;color:#ffffff;'
            .'text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;'
            .'">'.$actionLabel.'</a>'
        );

        return (new MailMessage)
            ->subject(__('messages.passwords.mail.subject'))
            ->greeting(__('messages.passwords.mail.greeting'))
            ->line(__('messages.passwords.mail.intro'))
            ->line($button)
            ->line(__('messages.passwords.mail.disclaimer'))
            ->salutation(__('messages.passwords.mail.salutation'));
    }
}
