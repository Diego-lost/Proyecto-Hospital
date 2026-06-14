<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailNotification extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'type',
        'subject',
        'status',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function recordSent(
        string $email,
        string $type,
        string $subject,
        ?User $user = null,
    ): self {
        return self::query()->create([
            'user_id' => $user?->id,
            'email' => $email,
            'type' => $type,
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public static function recordFailed(
        string $email,
        string $type,
        string $subject,
        string $error,
        ?User $user = null,
    ): self {
        return self::query()->create([
            'user_id' => $user?->id,
            'email' => $email,
            'type' => $type,
            'subject' => $subject,
            'status' => 'failed',
            'error_message' => $error,
            'sent_at' => now(),
        ]);
    }
}
