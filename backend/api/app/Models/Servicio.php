<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servicio extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'medico_id',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }
}
