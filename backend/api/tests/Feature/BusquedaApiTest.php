<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusquedaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_busqueda_medico_por_dni_devuelve_catalogo(): void
    {
        $esp = $this->postJson(route('api.especialidades.store'), ['nombre' => 'Neurología'])->assertCreated();
        $espId = $esp->json('id');

        $this->postJson(route('api.medicos.store'), [
            'nombre' => 'Dr. Busqueda',
            'dni' => '40604050',
            'especialidad_id' => $espId,
        ])->assertCreated();

        $this->getJson(route('api.busqueda.medico', ['dni' => '40604050']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('medico.nombre', 'Dr. Busqueda')
            ->assertJsonPath('medico.especialidad.nombre', 'Neurología');
    }

    public function test_busqueda_medico_dni_corto_422(): void
    {
        $this->getJson(route('api.busqueda.medico', ['dni' => '12']))
            ->assertStatus(422);
    }

    public function test_busqueda_paciente_por_dni_datos_previos(): void
    {
        $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'Ana Paciente',
            'paciente_dni' => '12345678',
            'telefono' => '555',
            'email' => 'ana@example.com',
            'origen' => 'web',
        ])->assertCreated();

        $this->getJson(route('api.busqueda.paciente', ['dni' => '12345678']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'Ana Paciente')
            ->assertJsonPath('datos.telefono', '555')
            ->assertJsonPath('fuente', 'local');
    }

    public function test_busqueda_paciente_sin_historial(): void
    {
        Config::set('services.consultasperu.token', '');

        $this->getJson(route('api.busqueda.paciente', ['dni' => '99999999']))
            ->assertOk()
            ->assertJsonPath('encontrado', false)
            ->assertJsonPath('detalle', 'sin_token');
    }

    public function test_busqueda_paciente_consultasperu_sin_historial(): void
    {
        Config::set('services.consultasperu.token', 'token-test');
        Http::fake([
            'api.consultasperu.com/*' => Http::response([
                'success' => true,
                'message' => 'Successful response',
                'data' => [
                    'number' => '11111111',
                    'full_name' => 'MARIA RENIEC PRUEBA',
                    'name' => 'MARIA',
                    'surname' => 'RENIEC PRUEBA',
                ],
            ], 200),
        ]);

        $this->getJson(route('api.busqueda.paciente', ['dni' => '11111111']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'MARIA RENIEC PRUEBA')
            ->assertJsonPath('fuente', 'consultasperu');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.consultasperu.com/api/v1/query'
                && $request['token'] === 'token-test'
                && $request['type_document'] === 'dni'
                && $request['document_number'] === '11111111';
        });
    }

    public function test_busqueda_reniec_directa(): void
    {
        Config::set('services.consultasperu.token', 'token-test');
        Http::fake([
            'api.consultasperu.com/*' => Http::response([
                'success' => true,
                'message' => 'Successful response',
                'data' => [
                    'full_name' => 'MEDICO API PRUEBA',
                ],
            ], 200),
        ]);

        $this->getJson(route('api.busqueda.reniec', ['dni' => '22222222']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'MEDICO API PRUEBA')
            ->assertJsonPath('fuente', 'consultasperu');
    }
}
