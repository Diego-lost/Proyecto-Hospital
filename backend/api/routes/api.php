<?php

/**
 * API JSON (prefijo /api).
 *
 * Público: catálogo (especialidades, médicos, servicios), estado y solicitudes de cita.
 * El panel Blade en /admin usa otros controladores (web).
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\ServicioController;

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
});
