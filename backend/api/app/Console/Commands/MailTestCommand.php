<?php

namespace App\Console\Commands;

use App\Support\MailDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {email : Correo destino}';

    protected $description = 'Envía un correo de prueba para verificar la configuración SMTP';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (! MailDelivery::isConfigured()) {
            $this->error('Correo SMTP incompleto en .env (MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS).');
            $this->line(MailDelivery::configurationHint());

            return self::FAILURE;
        }

        $this->line('Enviando a: '.$email.' (funciona con @gmail.com, @continental.edu.pe y cualquier dominio).');

        try {
            Mail::raw(
                'Correo de prueba — Clínica NovaSalud. Si lees esto, SMTP funciona correctamente.',
                fn ($message) => $message->to($email)->subject('Prueba de correo — Clínica NovaSalud'),
            );
        } catch (\Throwable $e) {
            $this->error('Error al enviar: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Correo de prueba enviado a {$email}. Revisa bandeja y spam.");

        return self::SUCCESS;
    }
}
