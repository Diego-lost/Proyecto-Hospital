<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthRedirect;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __($status),
                ], 422);
            }

            return back()->withErrors(['email' => __($status)]);
        }

        $user = User::query()->where('email', $validated['email'])->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Contraseña actualizada correctamente.',
                'user' => AuthRedirect::userPayload($user),
                'redirect_url' => AuthRedirect::forUser($user),
            ]);
        }

        return redirect()->to(AuthRedirect::forUser($user))
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
