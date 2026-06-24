<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCita;
use App\Support\DniPeru;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'paciente_dni' => ['required', 'string', 'regex:/^\d{7,8}$/'],
            'paciente_direccion' => ['required', 'string', 'max:500'],
            'especialidad' => ['nullable', 'string', 'max:80'],
            'medico_id' => ['nullable', 'integer', 'exists:medicos,id'],
            'fecha' => ['nullable', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'triage_riesgo' => ['nullable', 'string', 'in:bajo,medio,alto'],
            'triage_accion' => ['nullable', 'string', 'in:autocuidado,consulta_24h,urgencias'],
            'triage_resumen' => ['nullable', 'array'],
            'origen' => ['nullable', 'string', 'max:30'],
        ]);

        $data['origen'] = $data['origen'] ?? 'web';
        $data['estado'] = 'nueva';
        $data['prioridad'] = 'normal';
        $data['seguimiento_mensaje'] = null;

        $dniCanon = DniPeru::forReniecQuery($data['paciente_dni']);
        if ($dniCanon === null) {
            throw ValidationException::withMessages([
                'paciente_dni' => ['Indica un DNI de 7 u 8 dígitos (solo números).'],
            ]);
        }
        $data['paciente_dni'] = $dniCanon;

        if (($data['triage_riesgo'] ?? null) === 'alto' || ($data['triage_accion'] ?? null) === 'urgencias') {
            $data['prioridad'] = 'alta';
            $data['estado'] = 'prioritaria';
            $data['seguimiento_mensaje'] = 'Caso priorizado por triaje IA: contactaremos de inmediato y recomendamos acudir a emergencia si hay senales de alarma.';
        } elseif (($data['triage_riesgo'] ?? null) === 'medio') {
            $data['prioridad'] = 'media';
            $data['seguimiento_mensaje'] = 'Caso con prioridad media: se recomienda evaluacion medica en 24 horas.';
        } elseif (($data['triage_riesgo'] ?? null) === 'bajo') {
            $data['seguimiento_mensaje'] = 'Caso orientado a autocuidado con vigilancia de sintomas y seguimiento programado.';
        }

        $solicitud = SolicitudCita::create($data);

        return response()->json([
            'ok' => true,
            'solicitud' => $solicitud,
        ], 201);
    }

    public function comprobante(Request $request, SolicitudCita $solicitud)
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:160'],
        ]);

        $email = $data['email'] ?? null;
        if (is_string($email) && $email !== '' && $solicitud->email !== null) {
            if (strcasecmp(trim($email), trim((string) $solicitud->email)) !== 0) {
                return response()->json([
                    'message' => 'No se encontró el comprobante para los datos indicados.',
                ], 404);
            }
        }

        $solicitud->load([
            'medico:id,nombre,dni,especialidad_id',
            'medico.especialidad:id,nombre',
            'pago.servicio:id,nombre,descripcion,precio',
        ]);

        $pago = $solicitud->pago;
        $servicio = $pago?->servicio;
        $detallePaciente = is_array($solicitud->triage_resumen)
            ? ($solicitud->triage_resumen['datos_paciente'] ?? null)
            : null;

        return response()->json([
            'comprobante' => [
                'solicitud_id' => $solicitud->id,
                'estado_cita' => $solicitud->estado,
                'nombre' => $solicitud->nombre,
                'paciente_dni' => $solicitud->paciente_dni,
                'paciente_direccion' => $solicitud->paciente_direccion,
                'telefono' => $solicitud->telefono,
                'email' => $solicitud->email,
                'especialidad' => $solicitud->especialidad,
                'medico' => $solicitud->medico ? [
                    'nombre' => $solicitud->medico->nombre,
                    'especialidad' => $solicitud->medico->especialidad?->nombre,
                ] : null,
                'fecha' => $solicitud->fecha,
                'hora' => $solicitud->hora,
                'motivo' => $solicitud->motivo,
                'paciente_detalle' => $detallePaciente,
                'pago' => $pago ? [
                    'id' => $pago->id,
                    'estado' => $pago->estado,
                    'metodo' => $pago->metodo,
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                    'referencia_manual' => $pago->referencia_manual,
                    'paid_at' => $pago->paid_at?->toIso8601String(),
                ] : null,
                'servicio' => $servicio ? [
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'precio' => $servicio->precio,
                ] : null,
            ],
        ]);
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
