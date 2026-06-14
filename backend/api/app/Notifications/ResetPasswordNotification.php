<?php

namespace App\Notifications;

use App\Support\FrontendPublicUrl;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function resetUrl(mixed $notifiable): string
    {
        $base = rtrim(FrontendPublicUrl::resolve(), '/');

        return $base.'/#/restablecer-contrasena?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablecer contraseña — Clínica NovaSalud')
            ->greeting('Hola '.$notifiable->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace caduca en '.$expire.' minutos.')
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }
}
