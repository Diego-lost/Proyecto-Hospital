<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Origen del sitio estático (carpeta frontend del repo)
    |--------------------------------------------------------------------------
    |
    | Por defecto: dos niveles arriba de backend/api → …/ProyectoNuevo/frontend
    |
    */

    'source' => env('FRONTEND_SYNC_SOURCE', realpath(base_path('../../frontend')) ?: ''),

    /*
    |--------------------------------------------------------------------------
    | Destino dentro de public/ de Laravel
    |--------------------------------------------------------------------------
    |
    | Tras `php artisan frontend:sync`, abre por ejemplo:
    | …/backend/api/public/clinica/index.html
    |
    */

    'target_subdir' => env('FRONTEND_SYNC_TARGET', 'clinica'),

];
