<?php

namespace Tests\Feature;

use App\Models\AiInteractionLog;
use App\Models\Especialidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiEspecialidadSugerenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_clave_responde_503(): void
    {
        Config::set('ai.api_key', null);

        $this->postJson(route('api.ai.sugerir-especialidad'), [
            'motivo' => 'Dolor en el pecho al hacer esfuerzo',
        ])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('code', 'ai_disabled');

        $this->assertSame(0, AiInteractionLog::query()->count());
    }

    public function test_sin_especialidades_responde_422(): void
    {
        Config::set('ai.api_key', 'test-key');

        $this->postJson(route('api.ai.sugerir-especialidad'), [
            'motivo' => 'Consulta general',
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'empty_catalog');
    }

    public function test_con_ia_simulada_devuelve_sugerencias_y_registra_log(): void
    {
        Config::set('ai.api_key', 'test-key');
        Config::set('ai.base_url', 'https://api.openai.com/v1');
        Config::set('ai.model', 'gpt-test');

        Especialidad::query()->create([
            'nombre' => 'Medicina General',
            'imagen' => null,
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'sugerencias' => [[
                                'nombre' => 'Medicina General',
                                'confianza' => 0.82,
                                'notas' => 'Valoración inicial',
                            ]],
                            'mensaje_seguridad' => 'No es un diagnóstico.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => [
                    'prompt_tokens' => 40,
                    'completion_tokens' => 25,
                ],
            ], 200),
        ]);

        $this->postJson(route('api.ai.sugerir-especialidad'), [
            'motivo' => 'Necesito una revisión general',
        ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('modelo', 'gpt-test')
            ->assertJsonPath('sugerencias.0.nombre', 'Medicina General');

        Http::assertSentCount(1);

        $this->assertSame(1, AiInteractionLog::query()->count());
        $log = AiInteractionLog::query()->first();
        $this->assertTrue($log->ok);
        $this->assertSame('suggest_especialidad', $log->action);
        $this->assertSame(40, $log->prompt_tokens);
        $this->assertSame(25, $log->completion_tokens);
        $this->assertIsArray($log->result);
        $this->assertArrayHasKey('sugerencias', $log->result);
    }
}
