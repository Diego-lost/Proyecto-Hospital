<?php

namespace App\Notifications;

use App\Support\FrontendPublicUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(FrontendPublicUrl::resolve(), '/').'/#/login';

        return (new MailMessage)
            ->subject('Bienvenido a Clínica NovaSalud')
            ->greeting('Hola '.$notifiable->name)
            ->line('Tu cuenta fue creada correctamente.')
            ->line('Ya puedes iniciar sesión cuando quieras con tu correo y contraseña.')
            ->action('Ir al sitio', $url);
    }
}
