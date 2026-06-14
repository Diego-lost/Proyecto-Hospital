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

        $frontendHost = parse_url($frontend, PHP_URL_HOST);
        $appHost = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($frontendHost) || ! is_string($appHost)) {
            return false;
        }

        if (strcasecmp($frontendHost, $appHost) === 0) {
            return false;
        }

        $origin = $request->headers->get('Origin');
        if ($origin === null) {
            return $request->wantsJson();
        }

        return rtrim($origin, '/') === $frontend;
    }
}
