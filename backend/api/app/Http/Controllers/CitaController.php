<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudCita;

class CitaController extends Controller
{
    public function index()
    {
        return SolicitudCita::query()
            ->with(['medico:id,nombre,dni'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'paciente_dni' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'especialidad' => ['nullable', 'string', 'max:80'],
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'fecha' => ['nullable', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'origen' => ['nullable', 'string', 'max:30'],
        ]);

        $data['origen'] = $data['origen'] ?? 'web';
        $data['estado'] = 'nueva';
        if (empty($data['paciente_dni'])) {
            $data['paciente_dni'] = null;
        }

        $solicitud = SolicitudCita::create($data);

        return response()->json([
            'ok' => true,
            'solicitud' => $solicitud,
        ], 201);
    }

    public function cancelar(Request $request, SolicitudCita $solicitud)
    {
        if ($solicitud->estado === 'cancelada') {
            return response()->json([
                'ok' => false,
                'message' => 'La solicitud ya fue cancelada.',
            ], 409);
        }

        $data = $request->validate([
            'motivo_cancelacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $solicitud->estado = 'cancelada';
        $solicitud->motivo_cancelacion = $data['motivo_cancelacion'] ?? null;
        $solicitud->save();

        return response()->json([
            'ok' => true,
            'solicitud' => $solicitud,
        ]);
    }

    public function reprogramar(Request $request, SolicitudCita $solicitud)
    {
        if ($solicitud->estado === 'cancelada') {
            return response()->json([
                'ok' => false,
                'message' => 'No se puede reprogramar una solicitud cancelada.',
            ], 409);
        }

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        $solicitud->fecha = $data['fecha'];
        $solicitud->hora = $data['hora'];
        $solicitud->estado = 'reprogramada';
        $solicitud->save();

        return response()->json([
            'ok' => true,
            'solicitud' => $solicitud,
        ]);
    }
}
