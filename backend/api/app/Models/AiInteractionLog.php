<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteractionLog extends Model
{
    protected $fillable = [
        'action',
        'user_id',
        'solicitud_cita_id',
        'model',
        'input_sha256',
        'input_length',
        'result',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
        'ok',
        'error_code',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'ok' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solicitudCita(): BelongsTo
    {
        return $this->belongsTo(SolicitudCita::class);
    }
}
