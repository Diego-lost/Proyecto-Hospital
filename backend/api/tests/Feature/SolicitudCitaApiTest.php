<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudCitaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_una_solicitud_de_cita(): void
    {
        $res = $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'Juan Perez',
            'paciente_dni' => '40123456',
            'paciente_direccion' => 'Av. Principal 123, Lima',
            'telefono' => '999999999',
            'email' => 'juan@example.com',
            'especialidad' => 'Cardiología',
            'fecha' => '2026-04-27',
            'hora' => '10:30',
            'motivo' => 'Dolor de pecho',
            'origen' => 'web',
        ]);

        $res->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('solicitud.nombre', 'Juan Perez')
            ->assertJsonPath('solicitud.estado', 'nueva');

        $this->assertDatabaseHas('solicitudes_citas', [
            'nombre' => 'Juan Perez',
            'paciente_dni' => '40123456',
            'paciente_direccion' => 'Av. Principal 123, Lima',
            'telefono' => '999999999',
            'estado' => 'nueva',
        ]);
    }

    public function test_dni_de_siete_digitos_se_normaliza_a_ocho_en_bd(): void
    {
        $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'Pedro Siete',
            'paciente_dni' => '1234567',
            'paciente_direccion' => 'Urbanización Los Pinos',
            'telefono' => '987654321',
        ])->assertCreated();

        $this->assertDatabaseHas('solicitudes_citas', [
            'nombre' => 'Pedro Siete',
            'paciente_dni' => '01234567',
        ]);
    }

    public function test_lista_solicitudes_de_cita(): void
    {
        $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'A',
            'paciente_dni' => '11111111',
            'paciente_direccion' => 'Dir A',
            'telefono' => '1',
        ])->assertCreated();

        $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'B',
            'paciente_dni' => '22222222',
            'paciente_direccion' => 'Dir B',
            'telefono' => '2',
        ])->assertCreated();

        $res = $this->getJson(route('api.solicitudes-citas.index'));

        $res->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(2);
    }

    public function test_cancela_una_solicitud_de_cita(): void
    {
        $create = $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'A',
            'paciente_dni' => '33333333',
            'paciente_direccion' => 'Calle 1',
            'telefono' => '1',
        ])->assertCreated();

        $id = $create->json('solicitud.id');

        $cancel = $this->patchJson(route('api.solicitudes-citas.cancelar', ['solicitud' => $id]), [
            'motivo_cancelacion' => 'No podré asistir',
        ]);

        $cancel->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('solicitud.estado', 'cancelada');

        $this->assertDatabaseHas('solicitudes_citas', [
            'id' => $id,
            'estado' => 'cancelada',
        ]);
    }

    public function test_no_permite_cancelar_dos_veces(): void
    {
        $create = $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'A',
            'paciente_dni' => '33333333',
            'paciente_direccion' => 'Calle 1',
            'telefono' => '1',
        ])->assertCreated();

        $id = $create->json('solicitud.id');

        $this->patchJson(route('api.solicitudes-citas.cancelar', ['solicitud' => $id]))->assertOk();

        $this->patchJson(route('api.solicitudes-citas.cancelar', ['solicitud' => $id]))
            ->assertStatus(409);
    }

    public function test_reprograma_una_solicitud_de_cita(): void
    {
        $create = $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'A',
            'paciente_dni' => '33333333',
            'paciente_direccion' => 'Calle 1',
            'telefono' => '1',
        ])->assertCreated();

        $id = $create->json('solicitud.id');

        $res = $this->patchJson(route('api.solicitudes-citas.reprogramar', ['solicitud' => $id]), [
            'fecha' => '2026-05-01',
            'hora' => '15:00',
        ]);

        $res->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('solicitud.estado', 'reprogramada')
            ->assertJsonPath('solicitud.fecha', '2026-05-01');

        $hora = $res->json('solicitud.hora');
        $this->assertTrue(
            in_array($hora, ['15:00', '15:00:00'], true),
            "Hora esperada 15:00 o 15:00:00, recibido: {$hora}"
        );
    }
}
