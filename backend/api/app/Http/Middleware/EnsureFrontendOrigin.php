<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = $this->allowedOrigins();

        if ($allowed === []) {
            return $next($request);
        }

        $origin = $request->headers->get('Origin');
        if ($origin !== null && in_array(rtrim($origin, '/'), $allowed, true)) {
            return $next($request);
        }

        if ($origin === null && app()->environment('local')) {
            return $next($request);
        }

        return response()->json(['message' => 'Origen no permitido.'], 403);
    }

    /**
     * @return list<string>
     */
    private function allowedOrigins(): array
    {
        $origins = config('cors.allowed_origins', []);
        if (is_array($origins) && $origins !== [] && ! in_array('*', $origins, true)) {
            return array_values(array_filter(array_map(
                static fn ($o) => rtrim((string) $o, '/'),
                $origins,
            )));
        }

        $frontend = rtrim((string) config('app.frontend_url', ''), '/');

        return $frontend !== '' ? [$frontend] : [];
    }
}
