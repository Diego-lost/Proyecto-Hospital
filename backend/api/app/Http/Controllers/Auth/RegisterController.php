<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailNotification;
use App\Models\User;
use App\Support\AuthRedirect;
use App\Support\CrossOriginSpa;
use App\Support\FrontendPublicUrl;
use App\Support\MailDelivery;
use App\Support\SpaAuthToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    private const SUCCESS_MESSAGE = 'Te enviamos un correo de confirmación. Ya puedes volver a la plataforma: tu sesión quedó iniciada.';

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

        $user = null;

        try {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);
        } catch (QueryException) {
            $error = 'No se pudo conectar con la base de datos. En Render configura Supabase Session pooler (no la conexión directa db.xxx.supabase.co).';

            if ($request->wantsJson()) {
                return response()->json(['message' => $error], 503);
            }

            return back()->withErrors(['email' => $error])->withInput();
        }

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
            $payload = [
                'message' => self::SUCCESS_MESSAGE,
                'pending_verification' => true,
                'email' => $user->email,
                'user' => AuthRedirect::userPayload($user),
                'redirect_url' => AuthRedirect::forUser($user),
            ];

            $spaToken = null;
            if (CrossOriginSpa::usesStatelessAuth($request)) {
                $spaToken = SpaAuthToken::issue($user);
                $payload['token'] = $spaToken;
            }

            $payload['redirect_url'] = AuthRedirect::forUser($user, $spaToken);

            if ($spaToken === null) {
                Auth::login($user);
                $request->session()->regenerate();
            }

            return response()->json($payload, 201);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(FrontendPublicUrl::resolve())->with('status', self::SUCCESS_MESSAGE);
    }
}
