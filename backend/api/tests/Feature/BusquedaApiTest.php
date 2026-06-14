<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusquedaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.peruapi.key', '');
    }

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

    public function test_busqueda_medico_por_dni_sin_cero_inicial(): void
    {
        $esp = $this->postJson(route('api.especialidades.store'), ['nombre' => 'Cardiología'])->assertCreated();
        $espId = $esp->json('id');

        $this->postJson(route('api.medicos.store'), [
            'nombre' => 'Dra. Cero',
            'dni' => '01234567',
            'especialidad_id' => $espId,
        ])->assertCreated();

        $this->getJson(route('api.busqueda.medico', ['dni' => '1234567']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('medico.nombre', 'Dra. Cero');
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
            'paciente_direccion' => 'Jr. Los Olivos 45',
            'telefono' => '555',
            'email' => 'ana@example.com',
            'origen' => 'web',
        ])->assertCreated();

        $this->getJson(route('api.busqueda.paciente', ['dni' => '12345678']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'Ana Paciente')
            ->assertJsonPath('datos.telefono', '555')
            ->assertJsonPath('datos.direccion', 'Jr. Los Olivos 45')
            ->assertJsonPath('fuente', 'local');
    }

    public function test_busqueda_paciente_por_dni_sin_cero_inicial_en_bd(): void
    {
        $this->postJson(route('api.solicitudes-citas.store'), [
            'nombre' => 'Luis Paciente',
            'paciente_dni' => '01234567',
            'paciente_direccion' => 'Mz. A Lt. 2',
            'telefono' => '999',
            'email' => 'luis@example.com',
            'origen' => 'web',
        ])->assertCreated();

        $this->getJson(route('api.busqueda.paciente', ['dni' => '1234567']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'Luis Paciente')
            ->assertJsonPath('fuente', 'local');
    }

    public function test_busqueda_paciente_sin_historial_sin_peru_api_key(): void
    {
        $this->getJson(route('api.busqueda.paciente', ['dni' => '99999999']))
            ->assertOk()
            ->assertJsonPath('encontrado', false)
            ->assertJsonPath('detalle', 'sin_token');
    }

    public function test_busqueda_paciente_peru_api_sin_historial_local(): void
    {
        Config::set('services.peruapi.key', 'pk-test');
        Http::fake([
            'peruapi.com/api/dni/*' => Http::response([
                'dni' => '11111111',
                'cliente' => 'MARIA PERU API PRUEBA',
                'direccion' => 'Av. Test 999',
                'nombres' => 'MARIA',
                'apellido_paterno' => 'PERU',
                'apellido_materno' => 'API',
                'mensaje' => 'OK',
                'code' => '200',
            ], 200),
        ]);

        $this->getJson(route('api.busqueda.paciente', ['dni' => '11111111']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'MARIA PERU API PRUEBA')
            ->assertJsonPath('datos.direccion', 'Av. Test 999')
            ->assertJsonPath('fuente', 'peruapi');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'peruapi.com')
                && str_contains($request->url(), '/api/dni/11111111')
                && $request->method() === 'GET';
        });
    }

    public function test_busqueda_reniec_devuelve_mensaje_del_proveedor_en_401(): void
    {
        Config::set('services.peruapi.key', 'pk-test');
        Http::fake([
            'peruapi.com/api/dni/*' => Http::response([
                'mensaje' => 'API key inválida',
                'code' => '401',
            ], 401),
        ]);

        $this->getJson(route('api.busqueda.reniec', ['dni' => '33333333']))
            ->assertOk()
            ->assertJsonPath('encontrado', false)
            ->assertJsonPath('detalle', 'no_autorizado');
    }

    public function test_busqueda_reniec_directa(): void
    {
        Config::set('services.peruapi.key', 'pk-test');
        Http::fake([
            'peruapi.com/api/dni/*' => Http::response([
                'dni' => '22222222',
                'cliente' => 'MEDICO API PRUEBA',
                'direccion' => 'Calle Reniec 1',
                'mensaje' => 'OK',
                'code' => '200',
            ], 200),
        ]);

        $this->getJson(route('api.busqueda.reniec', ['dni' => '22222222']))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('datos.nombre', 'MEDICO API PRUEBA')
            ->assertJsonPath('datos.direccion', 'Calle Reniec 1')
            ->assertJsonPath('fuente', 'peruapi');
    }

    public function test_busqueda_reniec_siete_digitos_rellena_cero_para_api(): void
    {
        Config::set('services.peruapi.key', 'pk-test');
        Http::fake([
            'peruapi.com/api/dni/*' => Http::response([
                'dni' => '01234567',
                'cliente' => 'JUAN PRUEBA',
                'mensaje' => 'OK',
                'code' => '200',
            ], 200),
        ]);

        $this->getJson(route('api.busqueda.reniec', ['dni' => '1234567']))
            ->assertOk()
            ->assertJsonPath('encontrado', true);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/api/dni/01234567')
                && $request->method() === 'GET';
        });
    }
}
