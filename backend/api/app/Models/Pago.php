<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    public const ESTADO_PENDING = 'pending';

    public const ESTADO_PENDING_MANUAL = 'pending_manual';

    public const ESTADO_PAID = 'paid';

    public const ESTADO_CANCELLED = 'cancelled';

    public const ESTADO_EXPIRED = 'expired';

    public const METODO_TARJETA = 'tarjeta';

    public const METODO_YAPE = 'yape';

    public const METODO_TRANSFERENCIA = 'transferencia';

    protected $fillable = [
        'servicio_id',
        'solicitud_cita_id',
        'cliente_nombre',
        'cliente_email',
        'cliente_telefono',
        'monto',
        'moneda',
        'metodo',
        'estado',
        'stripe_checkout_session_id',
        'referencia_manual',
        'notas',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function marcarPagado(?string $notas = null): void
    {
        $this->estado = self::ESTADO_PAID;
        $this->paid_at = now();
        if ($notas !== null && $notas !== '') {
            $this->notas = $notas;
        }
        $this->save();
    }
}
