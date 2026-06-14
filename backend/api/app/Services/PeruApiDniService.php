<?php

namespace App\Services;

use App\Support\DniPeru;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consulta DNI vía Perú API (https://peruapi.com/) — REST GET /api/dni/{dni}?api_token=…
 */
class PeruApiDniService
{
    /**
     * @return array{encontrado: bool, nombre?: string, direccion?: string|null, detalle?: string|null, mensaje?: string|null, proveedor?: string}
     */
    public function consultarDni(string $dni): array
    {
        $key = (string) config('services.peruapi.key', '');
        if ($key === '') {
            return ['encontrado' => false, 'detalle' => 'sin_token'];
        }

        $canonical = DniPeru::forReniecQuery($dni);
        if ($canonical === null) {
            return ['encontrado' => false, 'detalle' => 'dni_invalido'];
        }

        $base = rtrim((string) config('services.peruapi.base_url', 'https://peruapi.com'), '/');
        $timeout = (int) config('services.peruapi.timeout', 15);
        $url = $base.'/api/dni/'.$canonical;

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($url, ['api_token' => $key]);
        } catch (\Throwable $e) {
            Log::warning('PeruApi: fallo de red al consultar DNI', [
                'dni' => $canonical,
                'message' => $e->getMessage(),
            ]);

            return ['encontrado' => false, 'detalle' => 'red'];
        }

        if ($response->status() === 401 || $response->status() === 403) {
            return [
                'encontrado' => false,
                'detalle' => 'no_autorizado',
                'mensaje' => self::mensajeProveedor($response),
            ];
        }

        if (! $response->successful()) {
            return [
                'encontrado' => false,
                'detalle' => 'error_http',
                'mensaje' => self::mensajeProveedor($response),
            ];
        }

        $json = $response->json();
        if (! is_array($json)) {
            return ['encontrado' => false, 'detalle' => 'sin_datos'];
        }

        $code = $json['code'] ?? null;
        $codeOk = $code === '200' || $code === 200;
        if (! $codeOk) {
            return [
                'encontrado' => false,
                'detalle' => 'sin_datos',
                'mensaje' => self::mensajeDesdeJson($json),
            ];
        }

        $nombre = trim((string) ($json['cliente'] ?? ''));
        if ($nombre === '') {
            $nombre = trim(
                trim((string) ($json['nombres'] ?? '')).' '
                .trim((string) ($json['apellido_paterno'] ?? '')).' '
                .trim((string) ($json['apellido_materno'] ?? ''))
            );
        }
        $nombre = preg_replace('/\s+/', ' ', trim($nombre)) ?? '';
        if ($nombre === '') {
            return [
                'encontrado' => false,
                'detalle' => 'sin_datos',
                'mensaje' => self::mensajeDesdeJson($json),
            ];
        }

        $direccion = self::direccionDesdeJson($json);

        return [
            'encontrado' => true,
            'nombre' => $nombre,
            'direccion' => $direccion,
            'proveedor' => 'peruapi',
        ];
    }

    /**
     * Algunos planes o proveedores incluyen domicilio en el JSON del DNI.
     *
     * @param  array<string, mixed>  $json
     */
    private static function direccionDesdeJson(array $json): ?string
    {
        $keys = [
            'direccion',
            'domicilio',
            'direccion_completa',
            'direccion_domicilio',
            'ubicacion',
            'address',
        ];
        foreach ($keys as $k) {
            $v = $json[$k] ?? null;
            if (! is_string($v)) {
                continue;
            }
            $t = trim($v);
            if ($t === '') {
                continue;
            }

            return preg_replace('/\s+/', ' ', $t) ?? $t;
        }

        return null;
    }

    private static function mensajeProveedor(Response $response): ?string
    {
        $json = $response->json();

        return is_array($json) ? self::mensajeDesdeJson($json) : null;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private static function mensajeDesdeJson(array $json): ?string
    {
        $m = $json['mensaje'] ?? $json['message'] ?? $json['msg'] ?? null;
        if (! is_string($m)) {
            return null;
        }
        $m = trim($m);

        return $m === '' ? null : $m;
    }
}
