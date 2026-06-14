<?php

namespace App\Http\Controllers;

use App\Services\Ai\EspecialidadSugerenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiEspecialidadController extends Controller
{
    public function sugerir(Request $request, EspecialidadSugerenciaService $ai): JsonResponse
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:2000'],
            'solicitud_cita_id' => ['nullable', 'integer', 'exists:solicitudes_citas,id'],
        ]);

        if (! $ai->isEnabled()) {
            return response()->json([
                'ok' => false,
                'code' => 'ai_disabled',
                'message' => 'El asistente de sugerencia no está configurado. Defina AI_API_KEY en el entorno.',
            ], 503);
        }

        try {
            $payload = $ai->sugerirDesdeMotivo(
                $data['motivo'],
                $data['solicitud_cita_id'] ?? null,
                $request->user()?->id,
            );
        } catch (RuntimeException $e) {
            return match ($e->getMessage()) {
                'empty_motivo' => response()->json([
                    'ok' => false,
                    'code' => 'empty_motivo',
                    'message' => 'El motivo no puede estar vacío.',
                ], 422),
                'empty_catalog' => response()->json([
                    'ok' => false,
                    'code' => 'empty_catalog',
                    'message' => 'No hay especialidades en catálogo.',
                ], 422),
                default => response()->json([
                    'ok' => false,
                    'code' => 'ai_error',
                    'message' => 'No se pudo obtener una sugerencia en este momento. Intente de nuevo más tarde.',
                ], 502),
            };
        }

        return response()->json([
            'ok' => true,
            'disclaimer' => $payload['disclaimer'],
            'modelo' => $payload['modelo'],
            'sugerencias' => $payload['sugerencias'],
        ]);
    }
}
