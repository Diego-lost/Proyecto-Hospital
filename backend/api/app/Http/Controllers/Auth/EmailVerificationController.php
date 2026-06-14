<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailNotification;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Support\FrontendPublicUrl;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Enlace de verificación inválido.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        Auth::login($user);
        $request->session()->regenerate();

        $base = rtrim(FrontendPublicUrl::resolve(), '/');

        return redirect()->away($base.'#/login?verificado=1');
    }

    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        if (! \App\Support\MailDelivery::isConfigured()) {
            return response()->json([
                'message' => 'El servidor no puede enviar correos reales todavía. '.\App\Support\MailDelivery::configurationHint(),
            ], 503);
        }

        $user = User::query()->where('email', $validated['email'])->first();
        $message = 'Revisa tu correo. Si aún no ingresaste, reenviamos la notificación con el enlace.';

        if (! $user || $user->hasVerifiedEmail()) {
            return response()->json(['message' => $message]);
        }

        $subject = 'Ingresa a tu cuenta — Clínica NovaSalud';

        try {
            $user->sendEmailVerificationNotification();
            EmailNotification::recordSent($user->email, 'email_verification', $subject, $user);
        } catch (\Throwable $e) {
            EmailNotification::recordFailed($user->email, 'email_verification', $subject, $e->getMessage(), $user);

            return response()->json([
                'message' => 'No pudimos enviar el correo. Verifica la configuración SMTP del servidor.',
            ], 503);
        }

        return response()->json(['message' => $message]);
    }
}
