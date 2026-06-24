<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuthRedirect;
use App\Support\CrossOriginSpa;
use App\Support\FrontendPublicUrl;
use App\Support\SpaAuthToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function csrf(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = SpaAuthToken::user($request->bearerToken());
        if ($user) {
            return response()->json(['user' => AuthRedirect::userPayload($user)]);
        }

        if (CrossOriginSpa::usesStatelessAuth($request)) {
            return response()->json(['user' => null]);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json(['user' => null]);
        }

        return response()->json(['user' => AuthRedirect::userPayload($user)]);
    }

    public function spaHandoff(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->away(rtrim(FrontendPublicUrl::resolve(), '/').'/#/');
        }

        $token = SpaAuthToken::issue($user);
        $frontend = rtrim(FrontendPublicUrl::resolve(), '/');

        return redirect()->away($frontend.'/?spa_token='.urlencode($token).'#/');
    }

    public function spaEnter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'redirect' => ['nullable', 'string'],
        ]);

        $user = SpaAuthToken::user($validated['token']);
        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'El enlace de acceso expiró. Vuelve a iniciar sesión.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Confirma tu correo antes de ingresar al panel.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($this->safeRedirectPath($validated['redirect'] ?? null, $user));
    }

    private function safeRedirectPath(?string $redirect, \App\Models\User $user): string
    {
        $path = trim((string) $redirect);
        if ($path === '' || ! str_starts_with($path, '/') || str_contains($path, '://')) {
            return $user->isAdmin()
                ? route('admin.dashboard', absolute: false)
                : FrontendPublicUrl::resolve();
        }

        if (str_starts_with($path, '/admin')) {
            return $user->isAdmin() ? $path : FrontendPublicUrl::resolve();
        }

        return $path;
    }
}
