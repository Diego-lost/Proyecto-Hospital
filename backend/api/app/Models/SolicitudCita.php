<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCita extends Model
{
    protected $table = 'solicitudes_citas';

    protected $fillable = [
        'nombre',
        'paciente_dni',
        'telefono',
        'email',
        'especialidad',
        'medico_id',
        'fecha',
        'hora',
        'motivo',
        'motivo_cancelacion',
        'estado',
        'origen',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }
}

