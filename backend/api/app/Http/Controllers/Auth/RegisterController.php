<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailNotification;
use App\Models\User;
use App\Support\MailDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    private const PENDING_MESSAGE = 'Revisa tu correo. Te enviamos una notificación con un enlace para ingresar a tu cuenta.';

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,admin'],
        ]);

        if (! MailDelivery::isConfigured()) {
            $error = 'El servidor aún no puede enviar correos. '.MailDelivery::configurationHint();

            if ($request->wantsJson()) {
                return response()->json(['message' => $error], 503);
            }

            return back()->withErrors(['email' => $error])->withInput();
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        $subject = 'Ingresa a tu cuenta — Clínica NovaSalud';

        try {
            $user->sendEmailVerificationNotification();
            EmailNotification::recordSent($user->email, 'email_verification', $subject, $user);
        } catch (\Throwable $e) {
            $user->delete();
            EmailNotification::recordFailed($validated['email'], 'email_verification', $subject, $e->getMessage());

            $error = 'No pudimos enviar la notificación a tu correo. Intenta de nuevo más tarde.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $error], 503);
            }

            return back()->withErrors(['email' => $error])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => self::PENDING_MESSAGE,
                'pending_verification' => true,
                'email' => $user->email,
            ], 201);
        }

        return redirect()->route('login')->with('status', self::PENDING_MESSAGE);
    }
}
