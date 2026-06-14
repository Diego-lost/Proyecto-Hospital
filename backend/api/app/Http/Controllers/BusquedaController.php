<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\SolicitudCita;
use App\Services\PeruApiDniService;
use App\Support\DniPeru;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    /**
     * Busca un médico por DNI (registrado en admin) y devuelve especialidad y servicios.
     */
    public function medicoPorDni(Request $request)
    {
        $dni = DniPeru::digitsOnly((string) $request->query('dni', ''));
        if (strlen($dni) < 4) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de al menos 4 caracteres.',
            ], 422);
        }

        $candidates = DniPeru::dbLookupCandidates((string) $request->query('dni', ''));
        $medico = Medico::query()
            ->whereIn('dni', $candidates)
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
    public function pacientePorDni(Request $request, PeruApiDniService $peruApiDni)
    {
        $dni = DniPeru::digitsOnly((string) $request->query('dni', ''));
        if (strlen($dni) < 4) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de al menos 4 caracteres.',
            ], 422);
        }

        $candidates = DniPeru::dbLookupCandidates((string) $request->query('dni', ''));

        $ultima = SolicitudCita::query()
            ->whereIn('paciente_dni', $candidates)
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
                    'direccion' => $ultima->paciente_direccion,
                ],
                'fuente' => 'local',
            ]);
        }

        $externo = $peruApiDni->consultarDni($dni);
        if ($externo['encontrado']) {
            return response()->json([
                'ok' => true,
                'encontrado' => true,
                'datos' => [
                    'nombre' => $externo['nombre'],
                    'telefono' => '',
                    'email' => null,
                    'direccion' => $externo['direccion'] ?? null,
                ],
                'fuente' => (string) ($externo['proveedor'] ?? 'peruapi'),
            ]);
        }

        return response()->json([
            'ok' => true,
            'encontrado' => false,
            'detalle' => $externo['detalle'] ?? 'sin_datos',
            'mensaje' => $externo['mensaje'] ?? null,
        ]);
    }

    /**
     * Consulta nombre por DNI (Perú API o Consultas Perú, según configuración).
     */
    public function reniecPorDni(Request $request, PeruApiDniService $peruApiDni)
    {
        $dni = (string) $request->query('dni', '');
        $canonical = DniPeru::forReniecQuery($dni);
        if ($canonical === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Indica un DNI de 7 u 8 dígitos (solo números).',
            ], 422);
        }

        $externo = $peruApiDni->consultarDni($canonical);
        if (! $externo['encontrado']) {
            return response()->json([
                'ok' => true,
                'encontrado' => false,
                'detalle' => $externo['detalle'] ?? 'sin_datos',
                'mensaje' => $externo['mensaje'] ?? null,
            ]);
        }

        return response()->json([
            'ok' => true,
            'encontrado' => true,
            'datos' => [
                'nombre' => $externo['nombre'],
                'direccion' => $externo['direccion'] ?? null,
            ],
            'fuente' => (string) ($externo['proveedor'] ?? 'peruapi'),
        ]);
    }
}
