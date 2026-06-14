<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuthRedirect;
use App\Support\FrontendPublicUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
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
