<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_route_returns_json(): void
    {
        $this->getJson(route('api.status'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('service', 'backend-api');
    }

    public function test_health_up_route(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_catalog_index_routes_respond(): void
    {
        $this->getJson(route('api.especialidades.index'))->assertOk()->assertJsonIsArray();
        $this->getJson(route('api.medicos.index'))->assertOk()->assertJsonIsArray();
        $this->getJson(route('api.servicios.index'))->assertOk()->assertJsonIsArray();
    }

    public function test_solicitudes_citas_index_empty(): void
    {
        $this->getJson(route('api.solicitudes-citas.index'))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(0);
    }

    public function test_busqueda_routes_responden(): void
    {
        $this->getJson(route('api.busqueda.medico', ['dni' => '1']))
            ->assertStatus(422);

        $this->getJson(route('api.busqueda.paciente', ['dni' => '1']))
            ->assertStatus(422);

        $this->getJson(route('api.busqueda.reniec', ['dni' => '1']))
            ->assertStatus(422);

        $this->getJson(route('api.busqueda.medico', ['dni' => '1234']))
            ->assertOk()
            ->assertJsonPath('encontrado', false);
    }
}
