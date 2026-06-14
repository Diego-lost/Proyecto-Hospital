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
});
