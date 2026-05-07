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

        // Subcarpeta (p. ej. XAMPP): deducir …/backend/api/public desde SCRIPT_NAME / base path.
        // No usar solo runningInConsole(): en PHPUnit los tests HTTP siguen en SAPI cli.
        $publicRootPath = self::laravelPublicRootFromRequest();
        if ($publicRootPath !== null) {
            $path = preg_replace('#/backend/api/public$#', '/frontend/', $publicRootPath);

            return request()->getSchemeAndHttpHost().$path;
        }

        $root = rtrim(URL::to('/'), '/');

        if (preg_match('#/backend/api/public$#', $root)) {
            return preg_replace('#/backend/api/public$#', '/frontend/', $root);
        }

        // artisan serve / raíz sin …/backend/api/public → el "sitio" no es esta URL.
        if (self::needsLocalStaticFallback($root)) {
            $fallback = config('app.frontend_url_fallback');
            if (is_string($fallback) && $fallback !== '') {
                return $fallback;
            }
        }

        return $root;
    }

    /**
     * true cuando la URL generada es solo la app Laravel en desarrollo (p. ej. :8000), no el HTML estático.
     */
    private static function needsLocalStaticFallback(string $root): bool
    {
        if (str_contains($root, '/frontend')) {
            return false;
        }

        $host = (string) (parse_url($root, PHP_URL_HOST) ?? '');
        $port = (int) (parse_url($root, PHP_URL_PORT) ?? 0);

        if (($host === '127.0.0.1' || $host === 'localhost') && $port >= 8000 && $port < 9010) {
            return true;
        }

        return false;
    }

    /**
     * Ruta absoluta en el servidor (sin host) hasta la carpeta `public` de la API, p. ej.
     * `/ProyectoNuevo/backend/api/public`, o null si no aplica.
     */
    private static function laravelPublicRootFromRequest(): ?string
    {
        $basePath = rtrim(str_replace('\\', '/', request()->getBasePath()), '/');
        if ($basePath !== '' && str_ends_with($basePath, '/backend/api/public')) {
            return $basePath;
        }

        $scriptName = request()->server('SCRIPT_NAME');
        if (! is_string($scriptName) || $scriptName === '') {
            return null;
        }

        $scriptName = str_replace('\\', '/', $scriptName);
        if (! str_contains($scriptName, '/backend/api/public')) {
            return null;
        }

        $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        return str_ends_with($dir, '/backend/api/public') ? $dir : null;
    }
}
