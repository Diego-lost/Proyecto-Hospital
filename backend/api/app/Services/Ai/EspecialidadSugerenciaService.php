<?php

namespace App\Services\Ai;

use App\Models\AiInteractionLog;
use App\Models\Especialidad;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EspecialidadSugerenciaService
{
    public function isEnabled(): bool
    {
        return filled(config('ai.api_key'));
    }

    /**
     * @return array{sugerencias: array<int, array<string, mixed>>, disclaimer: string, modelo: string}
     */
    public function sugerirDesdeMotivo(
        string $motivo,
        ?int $solicitudCitaId = null,
        ?int $userId = null,
    ): array {
        if (! $this->isEnabled()) {
            throw new RuntimeException('disabled');
        }

        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new RuntimeException('empty_motivo');
        }

        $nombresCatalogo = Especialidad::query()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->all();

        if ($nombresCatalogo === []) {
            throw new RuntimeException('empty_catalog');
        }

        $started = microtime(true);
        $model = (string) config('ai.model');
        $inputSha = hash('sha256', Str::lower($motivo));
        $inputLength = mb_strlen($motivo);

        $payload = $this->buildChatPayload($motivo, $nombresCatalogo, $model);

        try {
            $response = Http::timeout((int) config('ai.timeout'))
                ->withToken((string) config('ai.api_key'))
                ->acceptJson()
                ->asJson()
                ->post(config('ai.base_url').'/chat/completions', $payload);
        } catch (Throwable $e) {
            $this->logFailure(
                $model,
                $inputSha,
                $inputLength,
                $solicitudCitaId,
                $userId,
                'http_exception',
                (int) round((microtime(true) - $started) * 1000),
            );

            throw new RuntimeException('http_error', previous: $e);
        }

        $latencyMs = (int) round((microtime(true) - $started) * 1000);

        if (! $response->successful()) {
            $this->logFailure(
                $model,
                $inputSha,
                $inputLength,
                $solicitudCitaId,
                $userId,
                'http_'.$response->status(),
                $latencyMs,
            );

            throw new RuntimeException('http_error');
        }

        $body = $response->json();
        $content = data_get($body, 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'empty_content', $latencyMs);

            throw new RuntimeException('invalid_response');
        }

        $decoded = $this->decodeModelJson($content);
        if ($decoded === null) {
            $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'invalid_json', $latencyMs);

            throw new RuntimeException('invalid_json');
        }

        $sugerencias = $this->normalizeSugerencias($decoded, $nombresCatalogo);
        $disclaimer = is_string($decoded['mensaje_seguridad'] ?? null)
            ? (string) $decoded['mensaje_seguridad']
            : (string) config('ai.disclaimer_es');

        $promptTokens = data_get($body, 'usage.prompt_tokens');
        $completionTokens = data_get($body, 'usage.completion_tokens');

        AiInteractionLog::query()->create([
            'action' => 'suggest_especialidad',
            'user_id' => $this->aiLogUserId($userId),
            'solicitud_cita_id' => $solicitudCitaId,
            'model' => $model,
            'input_sha256' => $inputSha,
            'input_length' => $inputLength,
            'result' => [
                'sugerencias' => $sugerencias,
                'disclaimer' => $disclaimer,
            ],
            'prompt_tokens' => is_numeric($promptTokens) ? (int) $promptTokens : null,
            'completion_tokens' => is_numeric($completionTokens) ? (int) $completionTokens : null,
            'latency_ms' => $latencyMs,
            'ok' => true,
            'error_code' => null,
        ]);

        return [
            'sugerencias' => $sugerencias,
            'disclaimer' => $disclaimer,
            'modelo' => $model,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function inferEspecialidadDesdeTexto(string $motivo): ?array
    {
        try {
            $data = $this->sugerirDesdeMotivo($motivo);
        } catch (Throwable) {
            return null;
        }

        $first = $data['sugerencias'][0] ?? null;
        return is_array($first) ? $first : null;
    }

    /**
     * @param  list<string>  $nombresCatalogo
     */
    private function buildChatPayload(string $motivo, array $nombresCatalogo, string $model): array
    {
        $max = max(1, (int) config('ai.max_sugerencias'));
        $lista = implode("\n", array_map(static fn (string $n): string => '- '.$n, $nombresCatalogo));

        $system = <<<TXT
Eres un asistente de triaje administrativo para un centro de salud. Tu tarea es sugerir hasta {$max} especialidades del catálogo que encajen mejor con la descripción del paciente.
Reglas:
- Solo puedes usar nombres que aparezcan exactamente en el catálogo (copia literal).
- No des diagnósticos ni tratamientos. Usa lenguaje prudente ("podría orientarse a", "conviene valoración en").
- Responde ÚNICAMENTE con un JSON válido (sin markdown) con el esquema:
{"sugerencias":[{"nombre":"...del catálogo...","confianza":0.35,"notas":"breve"}],"mensaje_seguridad":"texto corto recordando que no es diagnóstico"}
- confianza es un número entre 0 y 1.
TXT;

        $user = "Catálogo de especialidades:\n{$lista}\n\nMotivo o síntomas descritos por el paciente:\n{$motivo}";

        $payload = [
            'model' => $model,
            'temperature' => 0.2,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        if (config('ai.json_object_response')) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    private function decodeModelJson(string $content): ?array
    {
        $trimmed = trim($content);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
            $trimmed = trim($trimmed);
        }

        $decoded = json_decode($trimmed, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  list<string>  $nombresCatalogo
     * @return list<array<string, mixed>>
     */
    private function normalizeSugerencias(array $decoded, array $nombresCatalogo): array
    {
        $raw = $decoded['sugerencias'] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        $byLower = [];
        foreach ($nombresCatalogo as $nombre) {
            $byLower[Str::lower($nombre)] = $nombre;
        }

        $max = max(1, (int) config('ai.max_sugerencias'));
        $out = [];

        foreach ($raw as $row) {
            if (count($out) >= $max) {
                break;
            }
            if (! is_array($row)) {
                continue;
            }
            $nombre = isset($row['nombre']) && is_string($row['nombre']) ? trim($row['nombre']) : '';
            if ($nombre === '') {
                continue;
            }
            $canonical = $byLower[Str::lower($nombre)] ?? null;
            if ($canonical === null) {
                continue;
            }

            $conf = $row['confianza'] ?? null;
            $confF = is_numeric($conf) ? (float) $conf : 0.0;
            $confF = max(0.0, min(1.0, $confF));

            $notas = isset($row['notas']) && is_string($row['notas']) ? trim($row['notas']) : '';

            $especialidad = Especialidad::query()->whereRaw('LOWER(nombre) = ?', [Str::lower($canonical)])->first();
            if ($especialidad === null) {
                continue;
            }

            $out[] = [
                'especialidad_id' => $especialidad->id,
                'nombre' => $especialidad->nombre,
                'confianza' => $confF,
                'notas' => Str::limit($notas, 500, ''),
            ];
        }

        return $out;
    }

    private function logFailure(
        string $model,
        string $inputSha,
        int $inputLength,
        ?int $solicitudCitaId,
        ?int $userId,
        string $errorCode,
        int $latencyMs,
    ): void {
        AiInteractionLog::query()->create([
            'action' => 'suggest_especialidad',
            'user_id' => $this->aiLogUserId($userId),
            'solicitud_cita_id' => $solicitudCitaId,
            'model' => $model,
            'input_sha256' => $inputSha,
            'input_length' => $inputLength,
            'result' => null,
            'prompt_tokens' => null,
            'completion_tokens' => null,
            'latency_ms' => $latencyMs,
            'ok' => false,
            'error_code' => $errorCode,
        ]);
    }

    private function aiLogUserId(?int $userId): ?int
    {
        if (config('database.ai_log_skip_user_id')) {
            return null;
        }

        return $userId;
    }
}
