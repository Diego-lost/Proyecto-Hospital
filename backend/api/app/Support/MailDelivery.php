<?php

namespace App\Support;

final class MailDelivery
{
    public static function usesRealTransport(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $mailer = (string) config('mail.default', 'log');

        return ! in_array($mailer, ['log', 'array'], true);
    }

    public static function configurationHint(): string
    {
        return 'Configura el REMITENTE en backend/api/.env (MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS). Ese es el correo de la clínica que envía; los pacientes pueden registrarse con cualquier email (@gmail, @continental, etc.). Luego: php artisan config:clear && php artisan mail:test destino@gmail.com';
    }

    public static function isConfigured(): bool
    {
        if (! self::usesRealTransport()) {
            return false;
        }

        $user = (string) config('mail.mailers.smtp.username', '');
        $pass = (string) config('mail.mailers.smtp.password', '');
        $from = (string) config('mail.from.address', '');

        return $user !== '' && $pass !== '' && $from !== '' && $from !== 'hello@example.com';
    }
}
