<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDirectionsService
{
    /**
     * @return array{
     *   ok: bool,
     *   distancia_metros?: int,
     *   duracion_segundos?: int,
     *   origen_direccion?: string|null,
     *   destino_direccion?: string|null,
     *   resumen?: string|null,
     *   detalle?: string|null,
     *   mensaje?: string|null,
     *   proveedor?: string
     * }
     */
    public function consultarRuta(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): array
    {
        $google = $this->consultarGoogle($origenLat, $origenLng, $destinoLat, $destinoLng);
        if ($google['ok']) {
            return array_merge($google, ['proveedor' => 'google']);
        }

        if (! $this->osrmFallbackEnabled()) {
            return $google;
        }

        $osrm = $this->consultarOsrm($origenLat, $origenLng, $destinoLat, $destinoLng);
        if ($osrm['ok']) {
            return array_merge($osrm, [
                'proveedor' => 'osrm',
                'mensaje' => $google['mensaje'] ?? null,
            ]);
        }

        return $google;
    }

    public function claveGoogleOperativa(): bool
    {
        $key = (string) config('services.google_maps.key', '');
        if ($key === '') {
            return false;
        }

        return (bool) Cache::remember('google_maps:key_ok', 300, function () {
            $origen = self::origenClinica();
            $result = $this->consultarGoogle(
                $origen['lat'],
                $origen['lng'],
                $origen['lat'] + 0.01,
                $origen['lng'] + 0.01,
            );

            return $result['ok'];
        });
    }

    /**
     * @return array{ok: bool, detalle?: string, mensaje?: string}
     */
    public function diagnosticoClave(): array
    {
        $key = (string) config('services.google_maps.key', '');
        if ($key === '') {
            return [
                'ok' => false,
                'detalle' => 'sin_api_key',
                'mensaje' => 'Falta GOOGLE_MAPS_API_KEY en backend/api/.env',
            ];
        }

        $origen = self::origenClinica();
        $result = $this->consultarGoogle(
            $origen['lat'],
            $origen['lng'],
            $origen['lat'] + 0.01,
            $origen['lng'] + 0.01,
        );

        if ($result['ok']) {
            return ['ok' => true];
        }

        return [
            'ok' => false,
            'detalle' => $result['detalle'] ?? 'clave_invalida',
            'mensaje' => self::mensajeClaveParaUsuario(
                $result['detalle'] ?? null,
                $result['mensaje'] ?? null,
            ),
        ];
    }

    public static function mensajeClaveParaUsuario(?string $detalle, ?string $mensajeRaw): string
    {
        $raw = strtolower(trim((string) $mensajeRaw));

        if ($detalle === 'sin_api_key') {
            return 'Falta configurar GOOGLE_MAPS_API_KEY en backend/api/.env.';
        }

        if (str_contains($raw, 'api project was not found') || str_contains($raw, 'project not found')) {
            return 'El proyecto de Google Cloud de tu clave fue eliminado o ya no existe.';
        }

        if (str_contains($raw, 'request denied') || str_contains($raw, 'not authorized')) {
            return 'Google rechazó la clave. Activa Directions API y Maps Embed API en tu proyecto.';
        }

        if (str_contains($raw, 'referer') || str_contains($raw, 'referrer')) {
            return 'La clave tiene restricción de sitio web que bloquea este servidor.';
        }

        if ($detalle === 'red') {
            return 'No hubo conexión con los servidores de Google Maps.';
        }

        return 'La clave de Google Maps configurada no funciona en este servidor.';
    }

    /**
     * @return array{
     *   ok: bool,
     *   distancia_metros?: int,
     *   duracion_segundos?: int,
     *   origen_direccion?: string|null,
     *   destino_direccion?: string|null,
     *   resumen?: string|null,
     *   detalle?: string|null,
     *   mensaje?: string|null
     * }
     */
    private function consultarGoogle(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): array
    {
        $key = (string) config('services.google_maps.key', '');
        if ($key === '') {
            return ['ok' => false, 'detalle' => 'sin_api_key', 'mensaje' => 'Falta GOOGLE_MAPS_API_KEY en .env del servidor.'];
        }

        $url = rtrim((string) config('services.google_maps.directions_url', 'https://maps.googleapis.com/maps/api/directions/json'), '/');
        $timeout = (int) config('services.google_maps.timeout', 20);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($url, [
                    'origin' => $origenLat.','.$origenLng,
                    'destination' => $destinoLat.','.$destinoLng,
                    'mode' => 'driving',
                    'key' => $key,
                ]);
        } catch (\Throwable $e) {
            Log::warning('GoogleDirections: fallo de red', ['message' => $e->getMessage()]);

            return ['ok' => false, 'detalle' => 'red', 'mensaje' => 'No se pudo contactar a Google Maps.'];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'detalle' => 'error_http',
                'mensaje' => 'Google Maps respondió con HTTP '.$response->status().'.',
            ];
        }

        $json = $response->json();
        if (! is_array($json)) {
            return ['ok' => false, 'detalle' => 'respuesta_invalida'];
        }

        $status = (string) ($json['status'] ?? '');
        if ($status !== 'OK') {
            return [
                'ok' => false,
                'detalle' => 'sin_ruta',
                'mensaje' => (string) ($json['error_message'] ?? $status),
            ];
        }

        $leg = $json['routes'][0]['legs'][0] ?? null;
        if (! is_array($leg)) {
            return ['ok' => false, 'detalle' => 'sin_ruta'];
        }

        $distancia = (int) ($leg['distance']['value'] ?? 0);
        $duracion = (int) ($leg['duration']['value'] ?? 0);
        $origen = isset($leg['start_address']) ? (string) $leg['start_address'] : null;
        $destino = isset($leg['end_address']) ? (string) $leg['end_address'] : null;
        $resumen = trim(($leg['distance']['text'] ?? '').' · '.($leg['duration']['text'] ?? ''));
        $polyline = (string) ($json['routes'][0]['overview_polyline']['points'] ?? '');

        return [
            'ok' => true,
            'distancia_metros' => $distancia,
            'duracion_segundos' => $duracion,
            'origen_direccion' => $origen,
            'destino_direccion' => $destino,
            'resumen' => $resumen !== '·' ? $resumen : null,
            'geometria' => self::geometriaDesdePolylineGoogle($polyline),
        ];
    }

    /**
     * @return array{type: string, coordinates: list<array{0: float, 1: float}>}|null
     */
    private static function geometriaDesdePolylineGoogle(string $encoded): ?array
    {
        if ($encoded === '') {
            return null;
        }

        $coordinates = [];
        $index = 0;
        $lat = 0;
        $lng = 0;
        $length = strlen($encoded);

        while ($index < $length) {
            $shift = 0;
            $result = 0;
            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $deltaLat = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $lat += $deltaLat;

            $shift = 0;
            $result = 0;
            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $deltaLng = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $lng += $deltaLng;

            $coordinates[] = [$lng / 1e5, $lat / 1e5];
        }

        return $coordinates === [] ? null : [
            'type' => 'LineString',
            'coordinates' => $coordinates,
        ];
    }

    /**
     * @return array{
     *   ok: bool,
     *   distancia_metros?: int,
     *   duracion_segundos?: int,
     *   resumen?: string|null,
     *   detalle?: string|null,
     *   mensaje?: string|null
     * }
     */
    private function consultarOsrm(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): array
    {
        $base = rtrim((string) config('services.google_maps.osrm_url', 'https://router.project-osrm.org'), '/');
        $timeout = (int) config('services.google_maps.timeout', 20);
        $path = sprintf(
            '/route/v1/driving/%s,%s;%s,%s',
            $origenLng,
            $origenLat,
            $destinoLng,
            $destinoLat,
        );

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($base.$path, [
                    'overview' => 'full',
                    'geometries' => 'geojson',
                    'steps' => 'false',
                ]);
        } catch (\Throwable $e) {
            Log::warning('OSRM: fallo de red', ['message' => $e->getMessage()]);

            return ['ok' => false, 'detalle' => 'red', 'mensaje' => 'No se pudo contactar al servicio de rutas alternativo.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'detalle' => 'error_http', 'mensaje' => 'El servicio de rutas alternativo no respondió.'];
        }

        $json = $response->json();
        if (! is_array($json) || ($json['code'] ?? '') !== 'Ok') {
            return ['ok' => false, 'detalle' => 'sin_ruta', 'mensaje' => 'No se encontró ruta alternativa.'];
        }

        $route = $json['routes'][0] ?? null;
        if (! is_array($route)) {
            return ['ok' => false, 'detalle' => 'sin_ruta'];
        }

        $distancia = (int) round((float) ($route['distance'] ?? 0));
        $duracion = (int) round((float) ($route['duration'] ?? 0));
        $geometria = $route['geometry'] ?? null;

        return [
            'ok' => true,
            'distancia_metros' => $distancia,
            'duracion_segundos' => $duracion,
            'resumen' => self::formatearResumenRuta($distancia, $duracion).' (OpenStreetMap)',
            'geometria' => is_array($geometria) ? $geometria : null,
        ];
    }

    private static function formatearResumenRuta(int $metros, int $segundos): string
    {
        $km = sprintf('%.1f km', $metros / 1000);
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $tiempo = $horas > 0
            ? sprintf('%d h %d min', $horas, $minutos)
            : $minutos.' min';

        return $km.' · '.$tiempo;
    }

    private function osrmFallbackEnabled(): bool
    {
        return filter_var(
            config('services.google_maps.osrm_fallback', true),
            FILTER_VALIDATE_BOOL,
        );
    }

    public static function origenClinica(): array
    {
        return [
            'lat' => (float) config('services.google_maps.origin_lat', -12.0653),
            'lng' => (float) config('services.google_maps.origin_lng', -75.2046),
            'ciudad' => (string) config('services.google_maps.origin_city', 'Huancayo'),
            'direccion' => (string) config('services.google_maps.origin_address', 'Av. Giráldez, Huancayo'),
        ];
    }
}
