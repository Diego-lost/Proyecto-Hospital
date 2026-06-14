<?php

namespace App\Support;

/**
 * Normalización de DNI peruano (8 dígitos) para consultas y coincidencias en BD.
 */
final class DniPeru
{
    public static function digitsOnly(string $input): string
    {
        return preg_replace('/\D+/', '', $input) ?? '';
    }

    /**
     * Valores a probar en columnas `dni` / `paciente_dni` (misma persona con distinta escritura).
     *
     * @return list<string>
     */
    public static function dbLookupCandidates(string $input): array
    {
        $d = self::digitsOnly($input);
        if ($d === '') {
            return [];
        }

        $out = [$d];
        if (strlen($d) === 7 && ctype_digit($d)) {
            $out[] = str_pad($d, 8, '0', STR_PAD_LEFT);
        }

        return array_values(array_unique($out));
    }

    /**
     * DNI de 8 dígitos para consulta externa (Perú API).
     * Acepta 7 dígitos (se asume un cero inicial omitido) u 8 dígitos.
     */
    public static function forReniecQuery(string $input): ?string
    {
        $d = self::digitsOnly($input);
        if ($d === '' || ! ctype_digit($d)) {
            return null;
        }
        $len = strlen($d);
        if ($len > 8) {
            return null;
        }
        if ($len < 7) {
            return null;
        }
        if ($len === 7) {
            return str_pad($d, 8, '0', STR_PAD_LEFT);
        }

        return $d;
    }
}
