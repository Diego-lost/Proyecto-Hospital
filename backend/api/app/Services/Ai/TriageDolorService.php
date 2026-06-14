<?php

namespace App\Services\Ai;

use App\Models\AiInteractionLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TriageDolorService
{
    private const ALERT_KEYWORDS = [
        'dolor toracico',
        'dolor de pecho',
        'falta de aire',
        'dificultad para respirar',
        'desmayo',
        'convulsion',
        'debilidad de un lado',
        'paralisis',
        'sangrado',
        'vomito con sangre',
        'heces negras',
    ];

    public function __construct(
        private readonly EspecialidadSugerenciaService $especialidadService,
        private readonly DxGptDiagnoseClient $dxgptClient,
    ) {}

    public function isEnabled(): bool
    {
        if (! (bool) config('ai.triage_enabled')) {
            return false;
        }

        if ($this->isDxgptProvider()) {
            return filled(config('ai.dxgpt_subscription_key')) && filled(config('ai.dxgpt_base_url'));
        }

        return filled(config('ai.api_key'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function evaluarDolor(array $data, ?int $userId = null): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('disabled');
        }

        $started = microtime(true);
        $model = $this->resolveModel();
        $symptomsText = $this->buildSymptomsText($data);
        $inputSha = hash('sha256', Str::lower($symptomsText));
        $inputLength = mb_strlen($symptomsText);
        $solicitudCitaId = isset($data['solicitud_cita_id']) && is_numeric($data['solicitud_cita_id'])
            ? (int) $data['solicitud_cita_id']
            : null;

        try {
            if ($this->isDxgptProvider()) {
                $responseJson = $this->dxgptClient->diagnose($symptomsText, $model);
            } else {
                $response = $this->sendOpenAiRequest($data, $model);
                if (! $response->successful()) {
                    $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'http_'.$response->status(), $started);
                    throw new RuntimeException('http_error');
                }
                $responseJson = $response->json();
            }
        } catch (RuntimeException $e) {
            $code = $e->getMessage();
            if (in_array($code, ['dxgpt_not_found', 'dxgpt_auth_error', 'dxgpt_timeout', 'dxgpt_script_missing', 'dxgpt_node_modules_missing', 'dxgpt_process_error', 'dxgpt_api_error', 'invalid_response'], true)) {
                $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, $code, $started);
                throw $e;
            }
            $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'http_exception', $started);
            throw new RuntimeException('http_error', previous: $e);
        } catch (Throwable $e) {
            $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'http_exception', $started);
            throw new RuntimeException('http_error', previous: $e);
        }

        $latencyMs = (int) round((microtime(true) - $started) * 1000);

        $decoded = $this->isDxgptProvider()
            ? $this->mapDxgptResponseToInternal($responseJson, $data)
            : $this->decodeOpenAiJson($responseJson);

        if (! is_array($decoded)) {
            $this->logFailure($model, $inputSha, $inputLength, $solicitudCitaId, $userId, 'invalid_response', $started);
            throw new RuntimeException('invalid_response');
        }

        $normalized = $this->normalizeResult($decoded, $data);
        $normalized['latency_ms'] = $latencyMs;
        $normalized['modelo'] = $model;

        $promptTokens = data_get($responseJson, 'usage.prompt_tokens');
        $completionTokens = data_get($responseJson, 'usage.completion_tokens');

        AiInteractionLog::query()->create([
            'action' => 'triage_dolor',
            'user_id' => $this->aiLogUserId($userId),
            'solicitud_cita_id' => $solicitudCitaId,
            'model' => $model,
            'input_sha256' => $inputSha,
            'input_length' => $inputLength,
            'result' => $normalized,
            'prompt_tokens' => is_numeric($promptTokens) ? (int) $promptTokens : null,
            'completion_tokens' => is_numeric($completionTokens) ? (int) $completionTokens : null,
            'latency_ms' => $latencyMs,
            'ok' => true,
            'error_code' => null,
        ]);

        return $normalized;
    }

    /**
     * Consulta libre: el paciente describe su molestia en una sola frase.
     *
     * @return array<string, mixed>
     */
    public function consultarLibre(string $mensaje, ?int $userId = null): array
    {
        $mensaje = trim($mensaje);
        if ($mensaje === '') {
            throw new RuntimeException('empty_message');
        }

        return $this->evaluarDolor([
            'motivo' => $mensaje,
            'edad' => 30,
            'intensidad_dolor' => 5,
            'duracion_horas' => 24,
            'ubicacion_dolor' => 'general',
            'sintomas_asociados' => [],
            'comorbilidades' => [],
        ], $userId);
    }

    private function isDxgptProvider(): bool
    {
        return Str::lower((string) config('ai.provider', 'openai')) === 'dxgpt';
    }

    private function resolveModel(): string
    {
        if ($this->isDxgptProvider()) {
            return (string) config('ai.dxgpt_model', 'gpt4o');
        }

        return (string) config('ai.model');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildSymptomsText(array $data): string
    {
        $sintomas = isset($data['sintomas_asociados']) && is_array($data['sintomas_asociados'])
            ? implode(', ', array_map(static fn ($s): string => (string) $s, $data['sintomas_asociados']))
            : '';
        $comorbilidades = isset($data['comorbilidades']) && is_array($data['comorbilidades'])
            ? implode(', ', array_map(static fn ($s): string => (string) $s, $data['comorbilidades']))
            : '';

        return trim(implode(' | ', [
            (string) ($data['motivo'] ?? ''),
            'ubicacion='.$data['ubicacion_dolor'],
            'intensidad='.$data['intensidad_dolor'],
            'duracion_horas='.$data['duracion_horas'],
            'sintomas='.$sintomas,
            'comorbilidades='.$comorbilidades,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildChatPayload(array $data, string $model): array
    {
        $system = <<<TXT
Eres un asistente clinico de orientacion inicial para dolor en Peru. No diagnostiques ni prescribas farmacos concretos.
Responde SOLO JSON valido con:
{
  "nivel_riesgo":"bajo|medio|alto",
  "accion_recomendada":"autocuidado|consulta_24h|urgencias",
  "senales_alarma":["..."],
  "recomendaciones_generales":["maximo 4 puntos, prudentes, no farmacos especificos"],
  "motivo_especialidad":"texto corto"
}
Si dudas o hay riesgo, prioriza seguridad.
TXT;

        $user = json_encode([
            'pais' => 'Peru',
            'edad' => $data['edad'] ?? null,
            'sexo' => $data['sexo'] ?? null,
            'embarazo' => $data['embarazo'] ?? null,
            'intensidad_dolor' => $data['intensidad_dolor'] ?? null,
            'duracion_horas' => $data['duracion_horas'] ?? null,
            'ubicacion_dolor' => $data['ubicacion_dolor'] ?? null,
            'sintomas_asociados' => $data['sintomas_asociados'] ?? [],
            'comorbilidades' => $data['comorbilidades'] ?? [],
            'motivo' => $data['motivo'] ?? '',
        ], JSON_UNESCAPED_UNICODE);

        $payload = [
            'model' => $model,
            'temperature' => 0.1,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => (string) $user],
            ],
        ];

        if (config('ai.json_object_response')) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $content): ?array
    {
        $trimmed = trim($content);
        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
            $trimmed = trim($trimmed);
        }
        $decoded = json_decode($trimmed, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendOpenAiRequest(array $data, string $model): \Illuminate\Http\Client\Response
    {
        $payload = $this->buildChatPayload($data, $model);

        return Http::timeout((int) config('ai.timeout'))
            ->withToken((string) config('ai.api_key'))
            ->acceptJson()
            ->asJson()
            ->post(config('ai.base_url').'/chat/completions', $payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendDxgptRequest(array $data, string $model): \Illuminate\Http\Client\Response
    {
        $baseUrl = rtrim((string) config('ai.dxgpt_base_url'), '/');
        $path = '/'.ltrim((string) config('ai.dxgpt_diagnose_path', '/diagnose'), '/');
        $payload = $this->buildDxgptPayload($data, $model);

        return Http::timeout((int) config('ai.timeout'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => (string) config('ai.dxgpt_subscription_key'),
                'Cache-Control' => 'no-cache',
            ])
            ->post($baseUrl.$path, $payload);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildDxgptPayload(array $data, string $model): array
    {
        $description = $this->buildSymptomsText($data);

        return [
            'description' => $description,
            'model' => $model,
            'myuuid' => (string) Str::uuid(),
            'timezone' => (string) config('ai.dxgpt_timezone', 'America/Lima'),
            'lang' => 'es',
        ];
    }

    /**
     * @param  mixed  $responseJson
     * @return array<string, mixed>|null
     */
    private function decodeOpenAiJson(mixed $responseJson): ?array
    {
        if (! is_array($responseJson)) {
            return null;
        }

        $content = data_get($responseJson, 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        return $this->decodeJson($content);
    }

    /**
     * @param  mixed  $responseJson
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>|null
     */
    private function mapDxgptResponseToInternal(mixed $responseJson, array $input): ?array
    {
        if (! is_array($responseJson)) {
            return null;
        }

        $diagnosisItems = data_get($responseJson, 'data');
        if (! is_array($diagnosisItems)) {
            $diagnosisItems = data_get($responseJson, 'differential_diagnosis');
        }
        if (! is_array($diagnosisItems)) {
            $diagnosisItems = data_get($responseJson, 'diagnoses');
        }
        if (! is_array($diagnosisItems)) {
            $diagnosisItems = data_get($responseJson, 'result.differential_diagnosis');
        }
        if (! is_array($diagnosisItems)) {
            $diagnosisItems = [];
        }

        $firstDiagnosis = null;
        $alertSignals = [];
        foreach ($diagnosisItems as $item) {
            if (! is_array($item)) {
                continue;
            }
            if ($firstDiagnosis === null) {
                $firstDiagnosis = $item;
            }
            $redFlags = $item['red_flags'] ?? $item['alarm_signs'] ?? null;
            if (is_array($redFlags)) {
                foreach ($redFlags as $flag) {
                    if (is_string($flag) && trim($flag) !== '') {
                        $alertSignals[] = trim($flag);
                    }
                }
            }
        }

        $confidence = is_numeric($firstDiagnosis['probability'] ?? null)
            ? (float) $firstDiagnosis['probability']
            : (is_numeric($firstDiagnosis['confidence'] ?? null) ? (float) $firstDiagnosis['confidence'] : 0.55);

        $risk = $this->riskFromDxgpt($confidence, $alertSignals);
        $action = match ($risk) {
            'alto' => 'urgencias',
            'medio' => 'consulta_24h',
            default => 'autocuidado',
        };

        $recs = [];
        $suggestions = data_get($responseJson, 'recommendations');
        if (is_array($suggestions)) {
            foreach ($suggestions as $s) {
                if (is_string($s) && trim($s) !== '') {
                    $recs[] = $this->sanitizeRecommendation($s);
                }
                if (count($recs) >= 4) {
                    break;
                }
            }
        }

        if ($recs === []) {
            $recs = [
                'Mantener reposo relativo y monitorear la evolucion del dolor.',
                'Si el dolor aumenta o aparecen sintomas nuevos, buscar atencion medica.',
            ];
        }

        $posiblesCausas = $this->mapDiagnosisItemsToCausas($diagnosisItems);

        return [
            'nivel_riesgo' => $risk,
            'accion_recomendada' => $action,
            'senales_alarma' => array_values(array_unique($alertSignals)),
            'recomendaciones_generales' => $recs,
            'intro' => $this->buildIntroText($input, $firstDiagnosis),
            'posibles_causas' => $posiblesCausas,
            'motivo_especialidad' => is_string($firstDiagnosis['reasoning'] ?? null)
                ? Str::limit(trim((string) $firstDiagnosis['reasoning']), 240, '')
                : (is_string($firstDiagnosis['description'] ?? null)
                    ? Str::limit(trim((string) $firstDiagnosis['description']), 240, '')
                    : (is_string($firstDiagnosis['diagnosis'] ?? null)
                        ? 'Posible '.trim((string) $firstDiagnosis['diagnosis'])
                        : 'Orientacion basada en descripcion clinica.')),
        ];
    }

    /**
     * @param  list<string>  $alertSignals
     */
    private function riskFromDxgpt(float $confidence, array $alertSignals): string
    {
        if ($alertSignals !== []) {
            return 'alto';
        }
        if ($confidence >= 0.65) {
            return 'medio';
        }

        return 'bajo';
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeResult(array $decoded, array $input): array
    {
        $risk = $this->normalizeRisk((string) ($decoded['nivel_riesgo'] ?? 'medio'));
        $action = $this->normalizeAction((string) ($decoded['accion_recomendada'] ?? 'consulta_24h'));

        $alerts = [];
        if (isset($decoded['senales_alarma']) && is_array($decoded['senales_alarma'])) {
            foreach ($decoded['senales_alarma'] as $signal) {
                if (is_string($signal) && trim($signal) !== '') {
                    $alerts[] = Str::limit(trim($signal), 160, '');
                }
            }
        }

        $hardAlert = $this->hasHardAlert($input);
        if ($hardAlert) {
            $risk = 'alto';
            $action = 'urgencias';
            $alerts[] = 'Posible senal de alarma detectada en sintomas. Acudir a emergencia.';
        }

        $recs = [];
        if (isset($decoded['recomendaciones_generales']) && is_array($decoded['recomendaciones_generales'])) {
            foreach ($decoded['recomendaciones_generales'] as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $safe = $this->sanitizeRecommendation($item);
                    if ($safe !== '') {
                        $recs[] = Str::limit($safe, 220, '');
                    }
                }
                if (count($recs) >= 4) {
                    break;
                }
            }
        }
        if ($recs === []) {
            $recs = ['Mantener reposo relativo y observar evolucion de sintomas.', 'Si el dolor aumenta o aparecen nuevos sintomas, buscar atencion medica.'];
        }

        $specialty = $this->especialidadService->inferEspecialidadDesdeTexto((string) ($input['motivo'] ?? ''));

        return [
            'nivel_riesgo' => $risk,
            'accion_recomendada' => $action,
            'senales_alarma' => array_values(array_unique($alerts)),
            'recomendaciones_generales' => $recs,
            'intro' => Str::limit((string) ($decoded['intro'] ?? $this->buildIntroText($input, null)), 600, ''),
            'posibles_causas' => is_array($decoded['posibles_causas'] ?? null)
                ? $this->sanitizePosiblesCausas($decoded['posibles_causas'])
                : [],
            'especialidad_sugerida' => $specialty['nombre'] ?? null,
            'especialidad_id' => $specialty['especialidad_id'] ?? null,
            'motivo_especialidad' => Str::limit((string) ($decoded['motivo_especialidad'] ?? 'Orientacion por sintomas reportados.'), 240, ''),
            'disclaimer_peru' => 'Orientacion informativa. No reemplaza evaluacion medica presencial. Si hay empeoramiento o senales de alarma, acuda a emergencia.',
        ];
    }

    private function normalizeRisk(string $risk): string
    {
        $x = Str::lower(trim($risk));
        return in_array($x, ['bajo', 'medio', 'alto'], true) ? $x : 'medio';
    }

    private function normalizeAction(string $action): string
    {
        $x = Str::lower(trim($action));
        return in_array($x, ['autocuidado', 'consulta_24h', 'urgencias'], true) ? $x : 'consulta_24h';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function hasHardAlert(array $input): bool
    {
        $text = Str::lower($this->buildSymptomsText($input));
        foreach (self::ALERT_KEYWORDS as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        $intensity = isset($input['intensidad_dolor']) && is_numeric($input['intensidad_dolor']) ? (int) $input['intensidad_dolor'] : 0;
        $duration = isset($input['duracion_horas']) && is_numeric($input['duracion_horas']) ? (int) $input['duracion_horas'] : 0;
        return $intensity >= 9 || $duration >= 72;
    }

    private function sanitizeRecommendation(string $text): string
    {
        $x = trim($text);
        $x = preg_replace('/\b(ibuprofeno|paracetamol|diclofenaco|naproxeno|amoxicilina)\b/i', 'medicacion indicada por profesional', $x) ?? $x;
        $x = preg_replace('/\b(diagnostico|confirmado)\b/i', 'evaluacion clinica', $x) ?? $x;
        return $x;
    }

    private function logFailure(
        string $model,
        string $inputSha,
        int $inputLength,
        ?int $solicitudCitaId,
        ?int $userId,
        string $errorCode,
        float $started
    ): void {
        $latencyMs = (int) round((microtime(true) - $started) * 1000);
        AiInteractionLog::query()->create([
            'action' => 'triage_dolor',
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

    /**
     * @param  array<string, mixed>|null  $firstDiagnosis
     */
    private function buildIntroText(array $input, ?array $firstDiagnosis): string
    {
        $motivo = trim((string) ($input['motivo'] ?? ''));
        $ubicacion = trim((string) ($input['ubicacion_dolor'] ?? ''));

        if ($motivo !== '') {
            $focus = mb_strtolower($motivo);
        } elseif ($ubicacion !== '') {
            $focus = 'dolor en '.$ubicacion;
        } else {
            $focus = 'malestar descrito';
        }

        $context = is_string($firstDiagnosis['description'] ?? null)
            ? Str::limit(trim((string) $firstDiagnosis['description']), 180, '')
            : null;

        if ($context !== null && $context !== '') {
            return "El {$focus} es una molestia frecuente en consulta ambulatoria. {$context} A continuación, posibles causas relacionadas con tu descripción:";
        }

        return "El {$focus} es una molestia frecuente que puede deberse a distintas causas. Según la información que compartiste, estas son las más probables:";
    }

    /**
     * @param  list<mixed>  $diagnosisItems
     * @return list<array{titulo: string, descripcion: string, sintomas_coincidentes: list<string>}>
     */
    private function mapDiagnosisItemsToCausas(array $diagnosisItems): array
    {
        $causas = [];
        foreach (array_slice($diagnosisItems, 0, 5) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $titulo = trim((string) ($item['diagnosis'] ?? $item['name'] ?? ''));
            $descripcion = trim((string) ($item['description'] ?? ''));
            if ($titulo === '' || $descripcion === '') {
                continue;
            }
            $sintomas = [];
            if (isset($item['symptoms_in_common']) && is_array($item['symptoms_in_common'])) {
                foreach ($item['symptoms_in_common'] as $s) {
                    if (is_string($s) && trim($s) !== '') {
                        $sintomas[] = Str::limit(trim($s), 80, '');
                    }
                }
            }
            $causas[] = [
                'titulo' => Str::limit($titulo, 120, ''),
                'descripcion' => Str::limit($descripcion, 520, ''),
                'sintomas_coincidentes' => $sintomas,
            ];
        }

        return $causas;
    }

    /**
     * @param  mixed  $items
     * @return list<array{titulo: string, descripcion: string, sintomas_coincidentes: list<string>}>
     */
    private function sanitizePosiblesCausas(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $causas = [];
        foreach (array_slice($items, 0, 5) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $titulo = trim((string) ($item['titulo'] ?? $item['diagnosis'] ?? $item['name'] ?? ''));
            $descripcion = trim((string) ($item['descripcion'] ?? $item['description'] ?? ''));
            if ($titulo === '' || $descripcion === '') {
                continue;
            }
            $sintomas = [];
            $rawSintomas = $item['sintomas_coincidentes'] ?? $item['symptoms_in_common'] ?? [];
            if (is_array($rawSintomas)) {
                foreach ($rawSintomas as $s) {
                    if (is_string($s) && trim($s) !== '') {
                        $sintomas[] = Str::limit(trim($s), 80, '');
                    }
                }
            }
            $causas[] = [
                'titulo' => Str::limit($titulo, 120, ''),
                'descripcion' => Str::limit($descripcion, 520, ''),
                'sintomas_coincidentes' => $sintomas,
            ];
        }

        return $causas;
    }
}
