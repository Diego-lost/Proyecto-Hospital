<?php

namespace App\Http\Controllers;

use App\Services\Ai\TriageDolorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiTriageController extends Controller
{
    public function evaluarDolor(Request $request, TriageDolorService $triage): JsonResponse
    {
        $data = $request->validate([
            'motivo' => ['required', 'string', 'max:2000'],
            'solicitud_cita_id' => ['nullable', 'integer', 'exists:solicitudes_citas,id'],
            'edad' => ['required', 'integer', 'min:0', 'max:120'],
            'sexo' => ['nullable', 'string', 'in:masculino,femenino,otro'],
            'embarazo' => ['nullable', 'boolean'],
            'intensidad_dolor' => ['required', 'integer', 'min:1', 'max:10'],
            'duracion_horas' => ['required', 'integer', 'min:0', 'max:720'],
            'ubicacion_dolor' => ['required', 'string', 'max:120'],
            'sintomas_asociados' => ['nullable', 'array', 'max:15'],
            'sintomas_asociados.*' => ['string', 'max:80'],
            'comorbilidades' => ['nullable', 'array', 'max:15'],
            'comorbilidades.*' => ['string', 'max:80'],
        ]);

        if (! $triage->isEnabled()) {
            $hint = config('ai.provider') === 'dxgpt'
                ? 'Defina DXGPT_BASE_URL y DXGPT_SUBSCRIPTION_KEY en el entorno.'
                : 'Defina AI_API_KEY en el entorno.';

            return response()->json([
                'ok' => false,
                'code' => 'ai_disabled',
                'message' => 'El asistente de triaje no está configurado. '.$hint,
            ], 503);
        }

        try {
            $result = $triage->evaluarDolor(
                $data,
                $request->user()?->id,
            );
        } catch (RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'dxgpt_not_found' => 'No se encontró el endpoint de DxGPT. Revise DXGPT_BASE_URL y DXGPT_DIAGNOSE_PATH.',
                'dxgpt_auth_error' => 'DxGPT rechazó la autenticación. Revise DXGPT_SUBSCRIPTION_KEY.',
                'dxgpt_timeout' => 'DxGPT tardó demasiado en responder. Intente de nuevo.',
                'dxgpt_node_modules_missing' => 'Falta instalar dependencias DxGPT: ejecute npm install en backend/api/scripts.',
                'dxgpt_script_missing' => 'No se encontró el script de integración DxGPT.',
                default => 'No se pudo completar el triaje en este momento. Intente de nuevo.',
            };
            return response()->json([
                'ok' => false,
                'code' => $e->getMessage(),
                'message' => $message,
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'triage' => $result,
        ]);
    }

    public function consulta(Request $request, TriageDolorService $triage): JsonResponse
    {
        $data = $request->validate([
            'mensaje' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        if (! $triage->isEnabled()) {
            $hint = config('ai.provider') === 'dxgpt'
                ? 'Defina DXGPT_BASE_URL y DXGPT_SUBSCRIPTION_KEY en el entorno.'
                : 'Defina AI_API_KEY en el entorno.';

            return response()->json([
                'ok' => false,
                'code' => 'ai_disabled',
                'message' => 'El asistente no está configurado. '.$hint,
            ], 503);
        }

        try {
            $result = $triage->consultarLibre(
                $data['mensaje'],
                $request->user()?->id,
            );
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'empty_message') {
                return response()->json([
                    'ok' => false,
                    'code' => 'empty_message',
                    'message' => 'Escribe tu consulta para poder ayudarte.',
                ], 422);
            }

            $message = match ($e->getMessage()) {
                'dxgpt_not_found' => 'No se encontró el endpoint de DxGPT. Revise DXGPT_BASE_URL y DXGPT_DIAGNOSE_PATH.',
                'dxgpt_auth_error' => 'DxGPT rechazó la autenticación. Revise DXGPT_SUBSCRIPTION_KEY.',
                'dxgpt_timeout' => 'El análisis tardó demasiado. Intente de nuevo.',
                'dxgpt_node_modules_missing' => 'Falta instalar dependencias DxGPT: ejecute npm install en backend/api/scripts.',
                'dxgpt_script_missing' => 'No se encontró el script de integración DxGPT.',
                default => 'No pude analizar tu consulta en este momento. Intente de nuevo.',
            };

            return response()->json([
                'ok' => false,
                'code' => $e->getMessage(),
                'message' => $message,
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'consulta' => $result,
        ]);
    }
}
