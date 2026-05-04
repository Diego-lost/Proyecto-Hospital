<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * URL del sitio estático NovaSalud (carpeta frontend).
 * Misma lógica que usa el panel admin para "Ver sitio web".
 */
final class FrontendPublicUrl
{
    public static function resolve(): string
    {
        $configured = config('app.frontend_url');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $root = rtrim(URL::to('/'), '/');

        if (preg_match('#/backend/api/public$#', $root)) {
            return preg_replace('#/backend/api/public$#', '/frontend/index.html', $root);
        }

        return $root;
    }
}
