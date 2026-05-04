<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncFrontendCommand extends Command
{
    protected $signature = 'frontend:sync';

    protected $description = 'Copia la carpeta frontend/ del repo a public/{clinica} para servir el mismo sitio bajo Laravel (Apache/XAMPP)';

    public function handle(): int
    {
        $source = config('frontend_sync.source');
        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
        $target = public_path($subdir);

        if ($source === '' || ! is_dir($source)) {
            $this->error('No se encontró la carpeta origen del frontend.');
            $this->line('Configura FRONTEND_SYNC_SOURCE en .env con la ruta absoluta a la carpeta frontend,');
            $this->line('o deja el proyecto en …/ProyectoNuevo/frontend respecto a backend/api.');

            return self::FAILURE;
        }

        $this->info('Origen:  '.$source);
        $this->info('Destino: '.$target);

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::ensureDirectoryExists(public_path());
        File::copyDirectory($source, $target);

        $this->components->info('Sincronización lista.');
        $this->line('Abre en el navegador la ruta pública, por ejemplo:');
        $this->line(rtrim(config('app.url'), '/').'/'.$subdir.'/index.html');

        return self::SUCCESS;
    }
}
