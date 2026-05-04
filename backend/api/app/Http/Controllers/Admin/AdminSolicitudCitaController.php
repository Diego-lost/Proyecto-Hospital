<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCita;
use Illuminate\Http\Request;

class AdminSolicitudCitaController extends Controller
{
    public function index()
    {
        $solicitudes = SolicitudCita::query()
            ->with(['medico:id,nombre,dni'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.solicitudes_citas.index', compact('solicitudes'));
    }

    public function cancelar(Request $request, SolicitudCita $solicitud)
    {
        $data = $request->validate([
            'motivo_cancelacion' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($solicitud->estado === 'cancelada') {
            return redirect()->route('admin.solicitudes-citas.index');
        }

        $solicitud->estado = 'cancelada';
        $solicitud->motivo_cancelacion = $data['motivo_cancelacion'] ?? null;
        $solicitud->save();

        return redirect()->route('admin.solicitudes-citas.index');
    }

    public function reprogramar(Request $request, SolicitudCita $solicitud)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        if ($solicitud->estado === 'cancelada') {
            return redirect()->route('admin.solicitudes-citas.index');
        }

        $solicitud->fecha = $data['fecha'];
        $solicitud->hora = $data['hora'];
        $solicitud->estado = 'reprogramada';
        $solicitud->save();

        return redirect()->route('admin.solicitudes-citas.index');
    }
}

