<?php

/**
 * API JSON (prefijo /api).
 *
 * Público: catálogo (especialidades, médicos, servicios), estado y solicitudes de cita.
 * IA: sugerencia de especialidad (requiere AI_API_KEY en servidor).
 * El panel Blade en /admin usa otros controladores (web).
 */

use App\Http\Controllers\AiEspecialidadController;
use App\Http\Controllers\AiTriageController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Middleware\EnsureFrontendOrigin;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'ok' => true,
            'service' => 'backend-api',
        ]);
    })->name('status');

    Route::prefix('busqueda')->name('busqueda.')->group(function () {
        Route::get('medico', [BusquedaController::class, 'medicoPorDni'])->name('medico');
        Route::get('paciente', [BusquedaController::class, 'pacientePorDni'])->name('paciente');
        Route::get('reniec', [BusquedaController::class, 'reniecPorDni'])->name('reniec');
    });

    Route::prefix('solicitudes-citas')->name('solicitudes-citas.')->group(function () {
        Route::get('/', [CitaController::class, 'index'])->name('index');
        Route::post('/', [CitaController::class, 'store'])->name('store');
        Route::patch('{solicitud}/cancelar', [CitaController::class, 'cancelar'])->name('cancelar');
        Route::patch('{solicitud}/reprogramar', [CitaController::class, 'reprogramar'])->name('reprogramar');
    });

    Route::apiResource('especialidades', EspecialidadController::class)
        ->parameters(['especialidades' => 'especialidad']);

    Route::apiResource('medicos', MedicoController::class)
        ->parameters(['medicos' => 'medico']);

    Route::apiResource('servicios', ServicioController::class)
        ->parameters(['servicios' => 'servicio']);

    Route::post('ai/sugerir-especialidad', [AiEspecialidadController::class, 'sugerir'])
        ->middleware('throttle:30,1')
        ->name('ai.sugerir-especialidad');
    Route::post('ai/triage-dolor', [AiTriageController::class, 'evaluarDolor'])
        ->middleware('throttle:20,1')
        ->name('ai.triage-dolor');
    Route::post('ai/consulta', [AiTriageController::class, 'consulta'])
        ->middleware('throttle:20,1')
        ->name('ai.consulta');

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('config', [PagoController::class, 'config'])->name('config');
        Route::post('checkout', [PagoController::class, 'checkout'])
            ->middleware('throttle:20,1')
            ->name('checkout');
        Route::post('manual', [PagoController::class, 'manual'])
            ->middleware('throttle:20,1')
            ->name('manual');
        Route::get('verificar', [PagoController::class, 'verificar'])->name('verificar');
        Route::get('{pago}', [PagoController::class, 'show'])->name('show');
    });

    Route::post('stripe/webhook', StripeWebhookController::class)
        ->name('stripe.webhook');

    /*
     * Auth SPA cross-origin (Firebase + API en Render): sin cookies/CSRF.
     * El front envía Origin y, tras login, Authorization: Bearer.
     */
    Route::prefix('auth')->name('auth.')->middleware([EnsureFrontendOrigin::class, 'throttle:30,1'])->group(function () {
        Route::post('register', [RegisterController::class, 'store'])->name('register');
        Route::post('login', [LoginController::class, 'store'])->name('login');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('forgot-password');
        Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('reset-password');
        Route::post('resend-verification', [EmailVerificationController::class, 'resend'])->name('resend-verification');
    });
});
