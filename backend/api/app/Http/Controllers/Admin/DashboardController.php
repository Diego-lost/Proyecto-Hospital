<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Servicio;
use App\Models\SolicitudCita;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
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

        return view('admin.dashboard', compact(
            'catalogo',
            'solicitudesTotal',
            'solicitudesUltimos30Dias',
            'solicitudesHoy',
            'solicitudesPorEstado',
            'proximasCitas',
        ));
    }
}

