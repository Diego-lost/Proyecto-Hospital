<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * URL del sitio público NovaSalud (React compilado en public/{subdir}, p. ej. public/clinica).
 * Misma lógica que usa el panel admin para "Ver sitio web". Origen del build: apps/web/dist + frontend:sync.
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
            return self::clinicaUnderPublicPath(request()->getSchemeAndHttpHost(), $publicRootPath);
        }

        $root = rtrim(URL::to('/'), '/');
        $root = self::preferRequestRootForDevServer($root);

        if (preg_match('#/backend/api/public$#', $root)) {
            return self::clinicaUnderPublicPathFromFullRoot($root);
        }

        // php artisan serve (:8000…): preferir build sincronizado en public/{clinica}; si no hay, Vite (:5173).
        if (self::needsLocalStaticFallback($root) && self::rootLooksLikeArtisanServe($root)) {
            return self::localArtisanServePublicUrl($root);
        }

        if (self::needsLocalStaticFallback($root)) {
            $fallback = config('app.frontend_url_fallback');
            if (is_string($fallback) && $fallback !== '') {
                return $fallback;
            }
        }

        return $root;
    }

    /**
     * Misma carpeta que copia `php artisan frontend:sync` (config frontend_sync.target_subdir).
     */
    private static function clinicaUnderPublicPath(string $schemeAndHttpHost, string $laravelPublicBasePath): string
    {
        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
        $base = rtrim(str_replace('\\', '/', $laravelPublicBasePath), '/');

        return rtrim($schemeAndHttpHost, '/').$base.'/'.$subdir.'/';
    }

    private static function clinicaUnderPublicPathFromFullRoot(string $root): string
    {
        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');

        return rtrim($root, '/').'/'.$subdir.'/';
    }

    /**
     * Si APP_URL no lleva puerto pero entras por artisan serve (:8000), URL::to('/') no
     * detecta el entorno dev; usamos el host (y puerto) de la petición HTTP actual.
     */
    private static function preferRequestRootForDevServer(string $rootFromAppUrl): string
    {
        $parts = parse_url($rootFromAppUrl);
        if (! is_array($parts)) {
            return $rootFromAppUrl;
        }

        $path = isset($parts['path']) ? rtrim((string) $parts['path'], '/') : '';
        if ($path !== '' && $path !== '/') {
            return $rootFromAppUrl;
        }

        $portFromUrl = (int) ($parts['port'] ?? 0);
        if ($portFromUrl >= 8000 && $portFromUrl < 9010) {
            return $rootFromAppUrl;
        }

        $request = request();
        if (! $request || ! $request->hasHeader('Host')) {
            return $rootFromAppUrl;
        }

        $port = (int) $request->getPort();
        if ($port < 8000 || $port >= 9010) {
            return $rootFromAppUrl;
        }

        $host = (string) ($parts['host'] ?? '');
        if ($host !== '127.0.0.1' && $host !== 'localhost') {
            return $rootFromAppUrl;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/');
    }

    /**
     * true cuando la URL generada es solo la app Laravel en desarrollo (p. ej. :8000), no el HTML estático.
     */
    private static function needsLocalStaticFallback(string $root): bool
    {
        $path = (string) (parse_url($root, PHP_URL_PATH) ?? '');
        if (preg_match('#/apps/web/dist(?:/|$)#', $path) || preg_match('#/clinica(?:/|$)#', $path)) {
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
     * true cuando la URL de la app es típica de `php artisan serve` (puerto 8000–9009).
     */
    private static function rootLooksLikeArtisanServe(string $root): bool
    {
        $port = (int) (parse_url($root, PHP_URL_PORT) ?? 0);

        return $port >= 8000 && $port < 9010;
    }

    /**
     * Con solo `php artisan serve`, sirve el React ya copiado a public/{subdir} (frontend:sync).
     * En local, si Vite (:5173) está activo, se prefiere al build estático (suele quedar desactualizado).
     */
    private static function localArtisanServePublicUrl(string $root): string
    {
        $viteUrl = self::viteDevUrlFromLaravelRoot($root);

        if (app()->environment('local') && self::viteDevServerReachable($root)) {
            return $viteUrl;
        }

        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
        if ($subdir === '') {
            return $viteUrl;
        }

        $index = public_path($subdir.'/index.html');
        if (is_file($index)) {
            return rtrim($root, '/').'/'.$subdir.'/';
        }

        return $viteUrl;
    }

    /**
     * Comprueba si el dev server de Vite responde en el puerto 5173.
     */
    private static function viteDevServerReachable(string $root): bool
    {
        $parts = parse_url($root);
        $host = is_array($parts) ? (string) ($parts['host'] ?? '127.0.0.1') : '127.0.0.1';
        if ($host === 'localhost') {
            $host = '127.0.0.1';
        }

        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, 5173, $errno, $errstr, 0.25);
        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }

    /**
     * URL del dev server de Vite (mismo host que Laravel, puerto 5173).
     */
    private static function viteDevUrlFromLaravelRoot(string $root): string
    {
        $parts = parse_url($root);
        if (! is_array($parts)) {
            return 'http://127.0.0.1:5173/';
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '127.0.0.1';

        return $scheme.'://'.$host.':5173/';
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
