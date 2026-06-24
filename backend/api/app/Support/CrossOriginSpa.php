<?php

namespace App\Support;

use Illuminate\Http\Request;

class CrossOriginSpa
{
    public static function isRequest(Request $request): bool
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($frontend === '' || $appUrl === '') {
            return false;
        }

        $frontendOrigin = self::originFromUrl($frontend);
        $appOrigin = self::originFromUrl($appUrl);

        if ($frontendOrigin === null || $appOrigin === null) {
            return false;
        }

        if (strcasecmp($frontendOrigin, $appOrigin) === 0) {
            return false;
        }

        $origin = $request->headers->get('Origin');
        if ($origin === null) {
            return $request->wantsJson();
        }

        return rtrim($origin, '/') === $frontend;
    }

    private static function originFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! isset($parts['host'])) {
            return null;
        }

        $scheme = (string) ($parts['scheme'] ?? 'http');
        $host = (string) $parts['host'];
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $defaultPort = $scheme === 'https' ? 443 : 80;

        if ($port === null || $port === $defaultPort) {
            return $scheme.'://'.$host;
        }

        return $scheme.'://'.$host.':'.$port;
    }

    /**
     * Auth por token (sin cookies) en rutas API o cuando el front está en otro origen.
     */
    public static function usesStatelessAuth(Request $request): bool
    {
        if (self::isRequest($request)) {
            return true;
        }

        if ($request->is('api/auth/*')) {
            return true;
        }

        return ! $request->hasSession();
    }
}
