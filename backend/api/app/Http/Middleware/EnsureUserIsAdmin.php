<?php

namespace App\Http\Middleware;

use App\Support\FrontendPublicUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }

            return redirect()->away(FrontendPublicUrl::resolve());
        }

        return $next($request);
    }
}
