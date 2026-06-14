<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncFrontendCommand extends Command
{
    protected $signature = 'frontend:sync';

    protected $description = 'Copia el build del sitio público (apps/web/dist por defecto) a public/{clinica} para servirlo bajo Laravel (Apache/XAMPP)';

    public function handle(): int
    {
        $source = config('frontend_sync.source');
        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
        $target = public_path($subdir);

        if ($source === '' || ! is_dir($source)) {
            $this->error('No se encontró la carpeta origen del sitio público.');
            $this->line('Ejecuta en apps/web: npm install && npm run build (genera apps/web/dist).');
            $this->line('O configura FRONTEND_SYNC_SOURCE en .env con la ruta absoluta a una carpeta dist/.');

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
