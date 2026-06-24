<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudCita extends Model
{
    protected $table = 'solicitudes_citas';

    protected $fillable = [
        'nombre',
        'paciente_dni',
        'paciente_direccion',
        'telefono',
        'email',
        'especialidad',
        'medico_id',
        'fecha',
        'hora',
        'motivo',
        'triage_riesgo',
        'triage_accion',
        'triage_resumen',
        'motivo_cancelacion',
        'estado',
        'prioridad',
        'seguimiento_mensaje',
        'origen',
    ];

    protected function casts(): array
    {
        return [
            'triage_resumen' => 'array',
        ];
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'solicitud_cita_id')->latestOfMany();
    }
}

