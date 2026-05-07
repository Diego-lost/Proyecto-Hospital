<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_especialidades_index_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.especialidades.index'))->assertOk();
    }

    public function test_admin_medicos_index_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.medicos.index'))->assertOk();
    }

    public function test_admin_servicios_index_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.servicios.index'))->assertOk();
    }

    public function test_admin_solicitudes_citas_index_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.solicitudes-citas.index'))->assertOk();
    }

    public function test_admin_especialidades_create_responde(): void
    {
        $this->actingAs($this->devPanelUser())->get(route('admin.especialidades.create'))->assertOk();
    }
}
