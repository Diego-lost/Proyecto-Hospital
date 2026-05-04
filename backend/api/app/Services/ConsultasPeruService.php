<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsultasPeruService
{
    /**
     * Consulta RENIEC vía CPE API (https://docs.consultasperu.com/api-consultas/dni).
     *
     * @return array{encontrado: bool, nombre?: string, detalle?: string|null}
     *               detalle (si !encontrado): sin_token|dni_invalido|red|no_autorizado|error_http|sin_datos
     */
    public function consultarDni(string $dni): array
    {
        $token = (string) config('services.consultasperu.token', '');
        if ($token === '') {
            return ['encontrado' => false, 'detalle' => 'sin_token'];
        }

        $dni = preg_replace('/\s+/', '', $dni);
        if (strlen($dni) !== 8 || ! ctype_digit($dni)) {
            return ['encontrado' => false, 'detalle' => 'dni_invalido'];
        }

        $url = (string) config('services.consultasperu.url');
        $timeout = (int) config('services.consultasperu.timeout', 15);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'token' => $token,
                    'type_document' => 'dni',
                    'document_number' => $dni,
                ]);
        } catch (\Throwable $e) {
            Log::warning('ConsultasPeru: fallo de red al consultar DNI', [
                'dni' => $dni,
                'message' => $e->getMessage(),
            ]);

            return ['encontrado' => false, 'detalle' => 'red'];
        }

        if ($response->status() === 401) {
            Log::warning('ConsultasPeru: token inválido o expirado');

            return ['encontrado' => false, 'detalle' => 'no_autorizado'];
        }

        // Documentación: 404 = "No data found" (DNI sin información).
        if ($response->status() === 404) {
            return ['encontrado' => false, 'detalle' => 'sin_datos'];
        }

        if (! $response->successful()) {
            return ['encontrado' => false, 'detalle' => 'error_http'];
        }

        $json = $response->json();
        if (! is_array($json) || empty($json['success']) || empty($json['data']) || ! is_array($json['data'])) {
            return ['encontrado' => false, 'detalle' => 'sin_datos'];
        }

        $data = $json['data'];
        $nombre = trim((string) ($data['full_name'] ?? ''));
        if ($nombre === '') {
            $nombre = trim(
                trim((string) ($data['name'] ?? '')).' '.trim((string) ($data['surname'] ?? ''))
            );
        }
        if ($nombre === '') {
            return ['encontrado' => false, 'detalle' => 'sin_datos'];
        }

        return ['encontrado' => true, 'nombre' => $nombre];
    }
}
