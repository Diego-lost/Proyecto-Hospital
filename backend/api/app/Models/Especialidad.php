<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Especialidad extends Model
{
    protected $fillable = ['nombre', 'imagen'];

    protected $table = 'especialidades';

    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class);
    }

    /**
     * Devuelve una URL accesible para el frontend.
     * Si el valor ya es URL http(s) lo deja tal cual; si es una ruta interna,
     * la convierte usando el disk configurado (public por defecto en local).
     */
    public function getImagenAttribute($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $v = trim($value);
        if (preg_match('#^https?://#i', $v) === 1) {
            return $v;
        }

        $disk = (string) config('filesystems.default', 'local');
        // Si local está apuntando a "private", usamos el disk público para que exista URL.
        if ($disk === 'local') {
            $disk = 'public';
        }

        return Storage::disk($disk)->url($v);
    }
}
