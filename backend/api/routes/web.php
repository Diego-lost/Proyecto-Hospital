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
use App\Http\Controllers\Admin\AdminPagoController;
use App\Http\Controllers\Admin\AdminSolicitudCitaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;

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

Route::get('/sitio-web', function () {
    return redirect()->away(FrontendPublicUrl::resolve());
})->name('web.public');

/** React (HashRouter): /clinica/pagar → /clinica/#/pagar (el build estático no tiene esas rutas en disco). */
$frontendSubdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
if ($frontendSubdir !== '') {
    Route::get($frontendSubdir.'/{spaPath}', function (string $spaPath) use ($frontendSubdir) {
        if (
            str_contains($spaPath, '.')
            || str_starts_with($spaPath, 'assets/')
            || str_starts_with($spaPath, 'img/')
        ) {
            abort(404);
        }

        return redirect('/'.$frontendSubdir.'/#/'.trim($spaPath, '/'));
    })->where('spaPath', '.*');
}

/** Solo depuración local: qué PHP y extensiones usa este mismo servidor (mismo que :8000). */
Route::get('/__nova/php-db-check', function () {
    if (! app()->isLocal() || ! config('app.debug')) {
        abort(404);
    }

    $iniPath = php_ini_loaded_file();
    $iniRaw = ($iniPath !== false && $iniPath !== '' && is_readable($iniPath)) ? (string) file_get_contents($iniPath) : '';
    $extDir = ini_get('extension_dir');
    $extDirNorm = $extDir !== false && $extDir !== '' ? rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $extDir), DIRECTORY_SEPARATOR) : '';
    $pdoDll = $extDirNorm !== '' ? $extDirNorm.DIRECTORY_SEPARATOR.'php_pdo_pgsql.dll' : '';
    $pgDll = $extDirNorm !== '' ? $extDirNorm.DIRECTORY_SEPARATOR.'php_pgsql.dll' : '';
    $phpDir = dirname(PHP_BINARY);
    $libpq = $phpDir.DIRECTORY_SEPARATOR.'libpq.dll';

    $iniActivePdoPgsql = $iniRaw !== '' && (bool) preg_match('/^\s*extension\s*=\s*pdo_pgsql\s*$/mi', $iniRaw);
    $iniCommentedPdoPgsql = $iniRaw !== '' && (bool) preg_match('/^\s*;\s*extension\s*=\s*pdo_pgsql\s*$/mi', $iniRaw);

    return response()->json([
        'php_version' => PHP_VERSION,
        'php_binary' => PHP_BINARY,
        'sapi' => PHP_SAPI,
        'ini_loaded_file' => $iniPath ?: null,
        'extension_dir' => $extDir !== false ? $extDir : null,
        'php_pdo_pgsql_dll_exists' => $pdoDll !== '' && is_file($pdoDll),
        'php_pgsql_dll_exists' => $pgDll !== '' && is_file($pgDll),
        'libpq_dll_next_to_php_exe' => is_file($libpq),
        'ini_line_extension_pdo_pgsql_present' => $iniActivePdoPgsql,
        'ini_line_semicolon_pdo_pgsql_present' => $iniCommentedPdoPgsql,
        'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql'),
        'pgsql_loaded' => extension_loaded('pgsql'),
        'pdo_drivers' => extension_loaded('pdo') ? \PDO::getAvailableDrivers() : [],
        'hint' => 'Si ini_line_extension_pdo_pgsql_present es false, en php.ini quita el ; de extension=pdo_pgsql y extension=pgsql. Si es true pero pdo_pgsql_loaded es false: cierra TODAS las ventanas de artisan serve (o mata el proceso php.exe del puerto 8000) y vuelve a arrancar; si sigue, ejecuta en CMD: C:\\xampp\\php\\php.exe --ri pdo_pgsql (debe decir "PDO Driver for PostgreSQL => enabled").',
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
})->name('dev.php-db-check');

Route::get('/auth/csrf', [AuthController::class, 'csrf'])->name('auth.csrf');
Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('guest')->group(function () {
    Route::post('/email/resend-verification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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

    Route::get('pagos', [AdminPagoController::class, 'index'])->name('pagos.index');
    Route::patch('pagos/{pago}/confirmar', [AdminPagoController::class, 'confirmar'])->name('pagos.confirmar');
});
