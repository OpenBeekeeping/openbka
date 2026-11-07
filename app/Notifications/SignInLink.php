<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignInLink extends Notification
{
    public function __construct(public string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sign-in link for '.config('app.name'))
            ->line('Use the button below to sign in. The link works once and expires in '.config('openbka.magic_link_ttl').' minutes.')
            ->action('Sign in', $this->url)
            ->line('If you did not ask to sign in, you can ignore this email.');
    }
}
