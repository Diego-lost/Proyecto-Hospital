<?php

namespace Tests\Unit;

use App\Services\GoogleDirectionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleDirectionsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('google_maps:key_ok');
    }

    public function test_usa_osrm_cuando_google_falla(): void
    {
        config([
            'services.google_maps.key' => 'clave-invalida',
            'services.google_maps.osrm_fallback' => true,
        ]);

        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'REQUEST_DENIED',
                'error_message' => 'clave invalida',
            ], 200),
            'router.project-osrm.org/*' => Http::response([
                'code' => 'Ok',
            'routes' => [[
                'distance' => 520000,
                'duration' => 28800,
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => [[-75.2046, -12.0653], [-75.2180, -12.0910]],
                ],
            ]],
            ], 200),
        ]);

        $result = app(GoogleDirectionsService::class)->consultarRuta(-12.0464, -77.0428, -13.5319, -71.9675);

        $this->assertTrue($result['ok']);
        $this->assertSame('osrm', $result['proveedor']);
        $this->assertSame(520000, $result['distancia_metros']);
        $this->assertIsArray($result['geometria'] ?? null);
    }

    public function test_traduce_error_de_clave_google_a_mensaje_amigable(): void
    {
        $mensaje = GoogleDirectionsService::mensajeClaveParaUsuario(
            'sin_ruta',
            'This API project was not found. This API project may have been deleted.',
        );

        $this->assertStringContainsString('proyecto de Google Cloud', $mensaje);
        $this->assertStringNotContainsString('API project was not found', $mensaje);
    }
}
