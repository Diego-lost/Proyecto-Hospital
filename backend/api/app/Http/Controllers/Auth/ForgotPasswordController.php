<?php



namespace App\Http\Controllers\Auth;



use App\Http\Controllers\Controller;

use App\Models\EmailNotification;

use App\Models\User;

use App\Notifications\ResetPasswordNotification;

use App\Support\MailDelivery;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Password;

use Illuminate\View\View;



class ForgotPasswordController extends Controller

{

    public function create(): View

    {

        return view('auth.forgot-password');

    }



    public function store(Request $request): RedirectResponse|JsonResponse

    {

        $validated = $request->validate([

            'email' => ['required', 'string', 'email'],

        ]);



        $email = $validated['email'];

        $user = User::query()->where('email', $email)->first();

        $subject = 'Restablecer contraseña — Clínica NovaSalud';

        $genericMessage = 'Esperando confirmación en tu correo. Si está registrado, te enviamos un enlace. Revisa tu bandeja de entrada y spam.';

        $smtpError = 'El servidor no puede enviar correos reales todavía. '.MailDelivery::configurationHint();



        if (! MailDelivery::isConfigured()) {

            if ($request->wantsJson()) {

                return response()->json(['message' => $smtpError], 503);

            }



            return back()->withErrors(['email' => $smtpError])->withInput();

        }



        if (! $user) {

            if ($request->wantsJson()) {

                return response()->json(['message' => $genericMessage]);

            }



            return back()->with('status', $genericMessage);

        }



        try {

            $token = Password::broker()->createToken($user);

            $user->notify(new ResetPasswordNotification($token));



            EmailNotification::recordSent($email, 'password_reset', $subject, $user);

        } catch (\Throwable $e) {

            EmailNotification::recordFailed($email, 'password_reset', $subject, $e->getMessage(), $user);



            if ($request->wantsJson()) {

                return response()->json([

                    'message' => 'No pudimos enviar el correo. Verifica la configuración SMTP del servidor.',

                ], 503);

            }



            return back()->withErrors([

                'email' => 'No pudimos enviar el correo. Contacta al administrador del sistema.',

            ]);

        }



        if ($request->wantsJson()) {

            return response()->json(['message' => $genericMessage]);

        }



        return back()->with('status', $genericMessage);

    }

}


