<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi Akun Ilmora Anda')
            ->view('emails.auth.reset-password', [
                'name' => $notifiable->name,
                'url' => $url,
                'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
            ]);
    }

    protected function resetUrl(object $notifiable): string
    {
        return config('app.url').'/reset-sandi?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
    }
}
