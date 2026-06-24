<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * @return array{lat: float, lng: float, display_name: string}|null
     */
    public function buscar(string $consulta): ?array
    {
        $consulta = trim($consulta);
        if ($consulta === '') {
            return null;
        }

        $ciudad = trim((string) config('services.google_maps.origin_city', 'Huancayo'));
        $busqueda = $this->enriquecerConsulta($consulta, $ciudad);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'NovaSaludClinica/1.0 (geocoding; contacto@novasalud.pe)',
                    'Accept-Language' => 'es',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $busqueda,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'pe',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Geocoding: fallo de red', ['message' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $items = $response->json();
        if (! is_array($items) || $items === []) {
            return null;
        }

        $item = $items[0];
        if (! is_array($item)) {
            return null;
        }

        $lat = isset($item['lat']) ? (float) $item['lat'] : null;
        $lng = isset($item['lon']) ? (float) $item['lon'] : null;
        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
            'display_name' => trim((string) ($item['display_name'] ?? $consulta)),
        ];
    }

    private function enriquecerConsulta(string $consulta, string $ciudad): string
    {
        $lower = mb_strtolower($consulta);
        if ($ciudad !== '' && ! str_contains($lower, mb_strtolower($ciudad))) {
            return $consulta.', '.$ciudad.', Junín, Perú';
        }

        if (! str_contains($lower, 'perú') && ! str_contains($lower, 'peru')) {
            return $consulta.', Perú';
        }

        return $consulta;
    }
}
