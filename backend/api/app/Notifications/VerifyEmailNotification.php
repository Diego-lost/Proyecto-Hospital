<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);
        $expire = Config::get('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('Ingresa a tu cuenta — Clínica NovaSalud')
            ->greeting('Hola '.$notifiable->name)
            ->line('Te registraste en Clínica NovaSalud. Haz clic en el botón para ingresar a tu cuenta.')
            ->action('Ingresar a mi cuenta', $url)
            ->line('Este enlace caduca en '.$expire.' minutos.')
            ->line('Si no creaste esta cuenta, ignora este mensaje.');
    }

    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }
}
