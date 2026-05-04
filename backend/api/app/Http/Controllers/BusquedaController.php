<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\SolicitudCita;
use App\Services\ConsultasPeruService;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    /**
     * Busca un médico por DNI (registrado en admin) y devuelve especialidad y servicios.
     */
    public function medicoPorDni(Request $request)
    {
        $dni = preg_replace('/\s+/', '', (string) $request->query('dni', ''));
        if (strlen($dni) < 4) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de al menos 4 caracteres.',
            ], 422);
        }

        $medico = Medico::query()
            ->where('dni', $dni)
            ->with(['especialidad', 'servicios'])
            ->first();

        if (! $medico) {
            return response()->json([
                'ok' => true,
                'encontrado' => false,
            ]);
        }

        return response()->json([
            'ok' => true,
            'encontrado' => true,
            'medico' => $medico,
        ]);
    }

    /**
     * Últimos datos de contacto registrados para ese DNI de paciente (solicitudes anteriores).
     */
    public function pacientePorDni(Request $request, ConsultasPeruService $consultasPeru)
    {
        $dni = preg_replace('/\s+/', '', (string) $request->query('dni', ''));
        if (strlen($dni) < 4) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de al menos 4 caracteres.',
            ], 422);
        }

        $ultima = SolicitudCita::query()
            ->where('paciente_dni', $dni)
            ->orderByDesc('id')
            ->first();

        if ($ultima) {
            return response()->json([
                'ok' => true,
                'encontrado' => true,
                'datos' => [
                    'nombre' => $ultima->nombre,
                    'telefono' => $ultima->telefono,
                    'email' => $ultima->email,
                ],
                'fuente' => 'local',
            ]);
        }

        $externo = $consultasPeru->consultarDni($dni);
        if ($externo['encontrado']) {
            return response()->json([
                'ok' => true,
                'encontrado' => true,
                'datos' => [
                    'nombre' => $externo['nombre'],
                    'telefono' => '',
                    'email' => null,
                ],
                'fuente' => 'consultasperu',
            ]);
        }

        return response()->json([
            'ok' => true,
            'encontrado' => false,
            'detalle' => $externo['detalle'] ?? 'sin_datos',
        ]);
    }

    /**
     * Solo consulta RENIEC vía Consultas Perú (útil para completar nombre del médico en admin).
     */
    public function reniecPorDni(Request $request, ConsultasPeruService $consultasPeru)
    {
        $dni = preg_replace('/\s+/', '', (string) $request->query('dni', ''));
        if (strlen($dni) !== 8 || ! ctype_digit($dni)) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de 8 dígitos.',
            ], 422);
        }

        $externo = $consultasPeru->consultarDni($dni);
        if (! $externo['encontrado']) {
            return response()->json([
                'ok' => true,
                'encontrado' => false,
                'detalle' => $externo['detalle'] ?? 'sin_datos',
            ]);
        }

        return response()->json([
            'ok' => true,
            'encontrado' => true,
            'datos' => [
                'nombre' => $externo['nombre'],
            ],
            'fuente' => 'consultasperu',
        ]);
    }
}
