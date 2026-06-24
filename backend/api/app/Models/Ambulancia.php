<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ambulancia extends Model
{
    public const ESTADO_DISPONIBLE = 'disponible';

    public const ESTADO_EN_RUTA = 'en_ruta';

    public const ESTADO_MANTENIMIENTO = 'mantenimiento';

    protected $fillable = [
        'codigo',
        'placa',
        'conductor',
        'estado',
        'origen_lat',
        'origen_lng',
        'destino_lat',
        'destino_lng',
        'destino_direccion',
        'distancia_metros',
        'duracion_segundos',
        'ruta_resumen',
        'despachada_at',
        'regreso_at',
    ];

    protected function casts(): array
    {
        return [
            'origen_lat' => 'float',
            'origen_lng' => 'float',
            'destino_lat' => 'float',
            'destino_lng' => 'float',
            'despachada_at' => 'datetime',
            'regreso_at' => 'datetime',
        ];
    }

    public function estaDisponible(): bool
    {
        return $this->estado === self::ESTADO_DISPONIBLE;
    }

    public function estaEnRuta(): bool
    {
        return $this->estado === self::ESTADO_EN_RUTA;
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            self::ESTADO_DISPONIBLE => 'Disponible',
            self::ESTADO_EN_RUTA => 'En ruta',
            self::ESTADO_MANTENIMIENTO => 'Mantenimiento',
            default => ucfirst((string) $this->estado),
        };
    }

    public function distanciaLegible(): ?string
    {
        if ($this->distancia_metros === null) {
            return null;
        }

        if ($this->distancia_metros >= 1000) {
            return number_format($this->distancia_metros / 1000, 1, '.', '').' km';
        }

        return $this->distancia_metros.' m';
    }

    public function duracionLegible(): ?string
    {
        if ($this->duracion_segundos === null) {
            return null;
        }

        $horas = intdiv($this->duracion_segundos, 3600);
        $minutos = intdiv($this->duracion_segundos % 3600, 60);

        if ($horas > 0) {
            return $horas.' h '.$minutos.' min';
        }

        return max($minutos, 1).' min';
    }
}
