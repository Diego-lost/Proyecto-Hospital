<?php

/**
 * Rutas web: página de inicio Laravel y panel de administración (Blade).
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\Especialidad;
use App\Support\FrontendPublicUrl;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminEspecialidadController;
use App\Http\Controllers\Admin\AdminMedicoController;
use App\Http\Controllers\Admin\AdminServicioController;
use App\Http\Controllers\Admin\AdminSolicitudCitaController;

Route::get('/', function () {
    $frontendRaw = FrontendPublicUrl::resolve();
    $frontend = rtrim($frontendRaw, '/');
    $laravel = rtrim(URL::to('/'), '/');

    if ($frontend !== $laravel) {
        return redirect()->away($frontendRaw);
    }

    $especialidades = Especialidad::query()->orderBy('nombre')->get();

    return view('home', compact('especialidades'));
})->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('especialidades', AdminEspecialidadController::class)
        ->parameters(['especialidades' => 'especialidad']);
    Route::resource('medicos', AdminMedicoController::class)
        ->parameters(['medicos' => 'medico']);
    Route::resource('servicios', AdminServicioController::class)
        ->parameters(['servicios' => 'servicio']);

    Route::get('solicitudes-citas', [AdminSolicitudCitaController::class, 'index'])
        ->name('solicitudes-citas.index');
    Route::patch('solicitudes-citas/{solicitud}/cancelar', [AdminSolicitudCitaController::class, 'cancelar'])
        ->name('solicitudes-citas.cancelar');
    Route::patch('solicitudes-citas/{solicitud}/reprogramar', [AdminSolicitudCitaController::class, 'reprogramar'])
        ->name('solicitudes-citas.reprogramar');
});
