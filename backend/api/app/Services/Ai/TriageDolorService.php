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
  "recomendaciones_generales":["entre 4 y 6 medidas concretas de alivio en casa: frio/calor, estiramiento, postura, elevacion, que evitar y cuando consultar. No uses solo reposo u observar."],
  "intro":"parrafo breve en espanol con acentos correctos",
  "posibles_causas":[{"titulo":"...","descripcion":"...","sintomas_coincidentes":["..."]}],
  "motivo_especialidad":"texto corto"
}
Responde en espanol de Peru con tildes correctas (á é í ó ú ñ).
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
            $recs = $this->buildReliefRecommendations($input);
        } else {
            $recs = $this->finalizeRecommendations($recs, $input);
        }

        $posiblesCausas = $this->mapDiagnosisItemsToCausas($diagnosisItems);

        return [
            'nivel_riesgo' => $risk,
            'accion_recomendada' => $action,
            'senales_alarma' => array_values(array_unique(array_map(fn ($s) => $this->textForUser((string) $s), $alertSignals))),
            'recomendaciones_generales' => $recs,
            'intro' => $this->textForUser($this->buildIntroText($input, $firstDiagnosis)),
            'posibles_causas' => $posiblesCausas,
            'motivo_especialidad' => $this->textForUser(
                is_string($firstDiagnosis['reasoning'] ?? null)
                    ? trim((string) $firstDiagnosis['reasoning'])
                    : (is_string($firstDiagnosis['description'] ?? null)
                        ? trim((string) $firstDiagnosis['description'])
                        : (is_string($firstDiagnosis['diagnosis'] ?? null)
                            ? 'Posible '.trim((string) $firstDiagnosis['diagnosis'])
                            : 'Orientación basada en descripción clínica.'))
            ),
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
            $alerts[] = 'Posible señal de alarma detectada en síntomas. Acudir a emergencia.';
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
            $recs = $this->buildReliefRecommendations($input);
        } else {
            $recs = $this->finalizeRecommendations($recs, $input);
        }

        $specialty = $this->especialidadService->inferEspecialidadDesdeTexto((string) ($input['motivo'] ?? ''));

        return [
            'nivel_riesgo' => $risk,
            'accion_recomendada' => $action,
            'senales_alarma' => array_values(array_unique(array_map(fn ($s) => $this->textForUser((string) $s), $alerts))),
            'recomendaciones_generales' => $recs,
            'intro' => $this->textForUser(Str::limit((string) ($decoded['intro'] ?? $this->buildIntroText($input, null)), 600, '')),
            'posibles_causas' => is_array($decoded['posibles_causas'] ?? null)
                ? $this->sanitizePosiblesCausas($decoded['posibles_causas'])
                : [],
            'especialidad_sugerida' => $specialty['nombre'] ?? null,
            'especialidad_id' => $specialty['especialidad_id'] ?? null,
            'motivo_especialidad' => $this->textForUser(Str::limit((string) ($decoded['motivo_especialidad'] ?? 'Orientación por síntomas reportados.'), 240, '')),
            'disclaimer_peru' => 'Orientación informativa. No reemplaza evaluación médica presencial. Si hay empeoramiento o señales de alarma, acuda a emergencia.',
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
        $x = $this->textForUser($text);
        $x = preg_replace('/\b(ibuprofeno|paracetamol|diclofenaco|naproxeno|amoxicilina)\b/i', 'medicación indicada por profesional', $x) ?? $x;
        $x = preg_replace('/\b(diagnóstico|diagnostico|confirmado)\b/i', 'evaluación clínica', $x) ?? $x;

        return $x;
    }

    private function textForUser(string $text): string
    {
        return Str::limit(trim($this->repairTextEncoding($text)), 520, '');
    }

    private function repairTextEncoding(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }

        if (preg_match('/[Ã├â]|├[│║¡®í░▒▓]/u', $text)) {
            $repaired = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
            if (is_string($repaired) && $repaired !== '' && mb_check_encoding($repaired, 'UTF-8')
                && preg_match('/[áéíóúñÁÉÍÓÚÑ]/u', $repaired)) {
                return $repaired;
            }
        }

        $map = [
            '├│' => 'ó', '├║' => 'ú', '├¡' => 'í', '├®' => 'é', '├í' => 'á', '├▒' => 'ñ',
            'Ã³' => 'ó', 'Ãº' => 'ú', 'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã±' => 'ñ',
            'Ã“' => 'Ó', 'Ãš' => 'Ú', 'Ã‘' => 'Ñ',
        ];

        return str_replace(array_keys($map), array_values($map), $text);
    }

    /**
     * @param  list<string>  $apiRecs
     * @param  array<string, mixed>  $input
     * @return list<string>
     */
    private function finalizeRecommendations(array $apiRecs, array $input): array
    {
        $localized = $this->buildReliefRecommendations($input);
        $apiRecs = array_values(array_filter(array_map(fn (string $r): string => $this->sanitizeRecommendation($r), $apiRecs)));

        if ($apiRecs === [] || $this->recommendationsAreGeneric($apiRecs)) {
            return array_slice($localized, 0, 6);
        }

        $merged = array_values(array_unique([...array_slice($localized, 0, 3), ...$apiRecs]));

        return array_slice($merged, 0, 6);
    }

    /**
     * @param  list<string>  $recs
     */
    private function recommendationsAreGeneric(array $recs): bool
    {
        foreach ($recs as $rec) {
            $lower = mb_strtolower($rec);
            if (! preg_match('/reposo|observar|evoluci[oó]n|atenci[oó]n m[eé]dica|monitorear|consultar/i', $lower)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return list<string>
     */
    private function buildReliefRecommendations(array $input): array
    {
        $text = mb_strtolower($this->buildSymptomsText($input));

        if (preg_match('/\b(pie|pies|tal[oó]n|planta|fascia|tobillo|metatarso)\b/u', $text)) {
            return [
                'Eleva el pie sobre un cojín cuando descanses, 15–20 minutos varias veces al día.',
                'Aplica hielo envuelto en un paño en la zona dolorida, 10–15 minutos (no directo sobre la piel).',
                'Estira la pantorrilla: con la pierna estirada, acerca los dedos hacia ti usando una toalla.',
                'Evita caminar descalzo en pisos duros; usa calzado con buen soporte del arco.',
                'Rueda suavemente una pelota de tenis o botella fría bajo el arco del pie con presión moderada.',
                'Si el dolor empeora al apoyar o no mejora en 3–5 días, agenda consulta con traumatología o medicina general.',
            ];
        }

        if (preg_match('/\b(cabeza|cefalea|migra|sien|nuca)\b/u', $text)) {
            return [
                'Descansa en un lugar tranquilo y con poca luz durante 20–30 minutos.',
                'Mantente hidratado; la deshidratación puede empeorar el dolor de cabeza.',
                'Coloca una compresa fría en frente o nuca si el dolor es pulsátil o intenso.',
                'Evita pantallas, cafeína en exceso y saltarte comidas mientras dure el episodio.',
                'Masajea suavemente sienes y cuello sin forzar movimientos bruscos.',
                'Consulta si el dolor es el peor de tu vida, hay fiebre, rigidez de cuello o confusión.',
            ];
        }

        if (preg_match('/\b(diente|muela|mand[ií]bula|enc[ií]a|boca)\b/u', $text)) {
            return [
                'Enjuaga con agua tibia y sal (1/2 cucharadita en un vaso) varias veces al día.',
                'Evita alimentos muy fríos, calientes o dulces que disparen el dolor.',
                'Mastica del lado contrario y prefiere alimentos blandos temporalmente.',
                'Mantén una higiene suave con cepillo de cerdas suaves sin presionar la zona sensible.',
                'Una compresa fría por fuera de la mejilla puede aliviar inflamación leve.',
                'Agenda odontología pronto si hay hinchazón, fiebre o dolor que no cede en 24–48 h.',
            ];
        }

        if (preg_match('/\b(lumbar|espalda|columna|cuello|cervical)\b/u', $text)) {
            return [
                'Evita cargar peso y posturas prolongadas encorvado frente al celular o PC.',
                'Aplica calor húmedo 15 minutos si sientes tensión muscular; frío si hubo golpe reciente.',
                'Realiza estiramientos suaves de espalda y cuello solo si no aumentan el dolor.',
                'Duerme con una almohada que mantenga el cuello alineado con la columna.',
                'Camina cortos trayectos para no inmovilizar la zona por horas.',
                'Busca valoración si hay dolor que baja a la pierna, entumecimiento o debilidad.',
            ];
        }

        return [
            'Identifica qué movimientos o posturas empeoran el dolor y evítalos de forma temporal.',
            'Usa frío 10–15 minutos si hay inflamación reciente; calor húmedo si predomina tensión muscular.',
            'Mantén hidratación y pausas activas cada hora si el dolor aparece con el esfuerzo.',
            'Realiza estiramientos suaves solo si no intensifican la molestia.',
            'Registra intensidad y duración para comentarlo con el médico.',
            'Consulta presencial si el dolor persiste más de 48–72 h, empeora o aparecen fiebre o debilidad.',
        ];
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
            ? Str::limit($this->repairTextEncoding(trim((string) $firstDiagnosis['description'])), 180, '')
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
            $titulo = $this->textForUser(trim((string) ($item['diagnosis'] ?? $item['name'] ?? '')));
            $descripcion = $this->textForUser(trim((string) ($item['description'] ?? '')));
            if ($titulo === '' || $descripcion === '') {
                continue;
            }
            $sintomas = [];
            if (isset($item['symptoms_in_common']) && is_array($item['symptoms_in_common'])) {
                foreach ($item['symptoms_in_common'] as $s) {
                    if (is_string($s) && trim($s) !== '') {
                        $sintomas[] = $this->textForUser(trim($s));
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
            $titulo = $this->textForUser(trim((string) ($item['titulo'] ?? $item['diagnosis'] ?? $item['name'] ?? '')));
            $descripcion = $this->textForUser(trim((string) ($item['descripcion'] ?? $item['description'] ?? '')));
            if ($titulo === '' || $descripcion === '') {
                continue;
            }
            $sintomas = [];
            $rawSintomas = $item['sintomas_coincidentes'] ?? $item['symptoms_in_common'] ?? [];
            if (is_array($rawSintomas)) {
                foreach ($rawSintomas as $s) {
                    if (is_string($s) && trim($s) !== '') {
                        $sintomas[] = $this->textForUser(trim($s));
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
