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
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (CrossOriginSpa::isRequest($request)) {
            return $this->storeCrossOriginJson($credentials);
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Revisa tu correo y haz clic en la notificación que te enviamos para poder ingresar.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'user' => AuthRedirect::userPayload($user),
                'redirect_url' => AuthRedirect::forUser($user),
            ]);
        }

        return redirect()->intended(AuthRedirect::forUser($user));
    }

    private function storeCrossOriginJson(array $credentials): JsonResponse
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Revisa tu correo y haz clic en la notificación que te enviamos para poder ingresar.',
            ]);
        }

        return response()->json([
            'user' => AuthRedirect::userPayload($user),
            'redirect_url' => AuthRedirect::forUser($user),
            'token' => SpaAuthToken::issue($user),
        ]);
    }

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        if (CrossOriginSpa::isRequest($request)) {
            SpaAuthToken::revoke($request->bearerToken());

            if ($request->wantsJson()) {
                return response()->json([
                    'redirect_url' => FrontendPublicUrl::resolve(),
                ]);
            }

            return redirect()->route('login');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'redirect_url' => FrontendPublicUrl::resolve(),
            ]);
        }

        return redirect()->route('login');
    }
}
