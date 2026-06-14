<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Servicio;
use App\Models\SolicitudCita;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function index(): View
    {
        $dashboardDbError = null;
        $catalogo = ['especialidades' => 0, 'medicos' => 0, 'servicios' => 0];
        $solicitudesTotal = 0;
        $solicitudesUltimos30Dias = 0;
        $solicitudesHoy = 0;
        $solicitudesPorEstado = new Collection;
        $proximasCitas = new Collection;

        try {
            $catalogo = [
                'especialidades' => Especialidad::query()->count(),
                'medicos' => Medico::query()->count(),
                'servicios' => Servicio::query()->count(),
            ];

            $solicitudesTotal = SolicitudCita::query()->count();
            $solicitudesUltimos30Dias = SolicitudCita::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $solicitudesHoy = SolicitudCita::query()
                ->whereDate('created_at', today())
                ->count();

            $solicitudesPorEstado = SolicitudCita::query()
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->orderByDesc('total')
                ->get();

            $proximasCitas = SolicitudCita::query()
                ->whereNotNull('fecha')
                ->whereDate('fecha', '>=', today())
                ->whereIn('estado', ['nueva', 'reprogramada'])
                ->orderBy('fecha')
                ->orderBy('hora')
                ->limit(8)
                ->get();
        } catch (Throwable $e) {
            Log::error('admin.dashboard_db', ['exception' => $e]);
            $dashboardDbError = config('app.debug')
                ? $e->getMessage()
                : 'No se pudo leer la base de datos (conexión o tablas). Revisa DB_* en .env, contraseña de Supabase, extensión pdo_pgsql en el PHP que usa Apache o artisan serve, y ejecuta php artisan migrate.';
        }

        return view('admin.dashboard', compact(
            'catalogo',
            'solicitudesTotal',
            'solicitudesUltimos30Dias',
            'solicitudesHoy',
            'solicitudesPorEstado',
            'proximasCitas',
            'dashboardDbError',
        ));
    }
}

