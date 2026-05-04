<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


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
}
