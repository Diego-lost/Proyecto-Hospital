<?php

namespace Tests\Unit;

use App\Services\GeocodingService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodingServiceTest extends TestCase
{
    public function test_busca_direccion_en_huancayo(): void
    {
        config([
            'services.google_maps.origin_city' => 'Huancayo',
        ]);

        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([[
                'lat' => '-12.0910',
                'lon' => '-75.2180',
                'display_name' => 'Avenida Progreso, El Tambo, Huancayo, Junín, Perú',
            ]]),
        ]);

        $result = app(GeocodingService::class)->buscar('Av. Progreso, El Tambo');

        $this->assertNotNull($result);
        $this->assertSame(-12.091, $result['lat']);
        $this->assertStringContainsString('Huancayo', $result['display_name']);
    }
}
