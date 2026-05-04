<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_especialidades(): void
    {
        $create = $this->postJson(route('api.especialidades.store'), [
            'nombre' => 'Cardiología',
            'imagen' => 'cardio.jpg',
        ])->assertCreated();

        $id = $create->json('id');

        $this->getJson(route('api.especialidades.index'))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(1);

        $this->getJson(route('api.especialidades.show', ['especialidad' => $id]))
            ->assertOk()
            ->assertJsonPath('nombre', 'Cardiología');

        $this->putJson(route('api.especialidades.update', ['especialidad' => $id]), [
            'nombre' => 'Cardiología (Edit)',
        ])->assertOk()
            ->assertJsonPath('nombre', 'Cardiología (Edit)');

        $this->deleteJson(route('api.especialidades.destroy', ['especialidad' => $id]))
            ->assertNoContent();
    }

    public function test_crud_medicos(): void
    {
        $esp = $this->postJson(route('api.especialidades.store'), ['nombre' => 'Pediatría'])->assertCreated();
        $especialidadId = $esp->json('id');

        $create = $this->postJson(route('api.medicos.store'), [
            'nombre' => 'Dra. Maria',
            'especialidad_id' => $especialidadId,
            'foto' => 'maria.jpg',
        ])->assertCreated();

        $id = $create->json('id');

        $this->getJson(route('api.medicos.index', ['especialidad_id' => $especialidadId]))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(1);

        $this->getJson(route('api.medicos.show', ['medico' => $id]))
            ->assertOk()
            ->assertJsonPath('nombre', 'Dra. Maria')
            ->assertJsonPath('especialidad.id', $especialidadId);

        $this->patchJson(route('api.medicos.update', ['medico' => $id]), [
            'nombre' => 'Dra. Maria (Edit)',
        ])->assertOk()
            ->assertJsonPath('nombre', 'Dra. Maria (Edit)');

        $this->deleteJson(route('api.medicos.destroy', ['medico' => $id]))
            ->assertNoContent();
    }

    public function test_crud_servicios(): void
    {
        $esp = $this->postJson(route('api.especialidades.store'), ['nombre' => 'Dermatología'])->assertCreated();
        $especialidadId = $esp->json('id');

        $med = $this->postJson(route('api.medicos.store'), [
            'nombre' => 'Dr. Diego',
            'especialidad_id' => $especialidadId,
        ])->assertCreated();
        $medicoId = $med->json('id');

        $create = $this->postJson(route('api.servicios.store'), [
            'nombre' => 'Consulta dermatológica',
            'descripcion' => 'Evaluación de piel',
            'precio' => 100,
            'medico_id' => $medicoId,
        ])->assertCreated();

        $id = $create->json('id');

        $this->getJson(route('api.servicios.index', ['medico_id' => $medicoId]))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(1);

        $this->getJson(route('api.servicios.show', ['servicio' => $id]))
            ->assertOk()
            ->assertJsonPath('nombre', 'Consulta dermatológica')
            ->assertJsonPath('medico.id', $medicoId);

        $this->putJson(route('api.servicios.update', ['servicio' => $id]), [
            'precio' => 120,
        ])->assertOk()
            ->assertJsonPath('precio', 120);

        $this->deleteJson(route('api.servicios.destroy', ['servicio' => $id]))
            ->assertNoContent();
    }
}
