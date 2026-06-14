<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Medico extends Model
{
    protected $fillable = [
        'nombre',
        'dni',
        'especialidad_id',
        'foto',
    ];

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    /**
     * Devuelve URL accesible para el frontend (foto del médico).
     * Si el valor ya es URL http(s), lo deja tal cual.
     */
    public function getFotoAttribute($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $v = trim($value);
        if (preg_match('#^https?://#i', $v) === 1) {
            return $v;
        }

        $disk = (string) config('filesystems.default', 'local');
        if ($disk === 'local') {
            $disk = 'public';
        }

        return Storage::disk($disk)->url($v);
    }
}
