<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SpaAuthToken
{
    private const TTL_SECONDS = 7200;

    public static function issue(User $user): string
    {
        $plain = Str::random(64);
        Cache::put(self::cacheKey($plain), $user->id, self::TTL_SECONDS);

        return $plain;
    }

    public static function user(?string $plain): ?User
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        $userId = Cache::get(self::cacheKey($plain));
        if ($userId === null) {
            return null;
        }

        return User::query()->find($userId);
    }

    public static function revoke(?string $plain): void
    {
        if ($plain === null || $plain === '') {
            return;
        }

        Cache::forget(self::cacheKey($plain));
    }

    private static function cacheKey(string $plain): string
    {
        return 'spa_auth:'.hash('sha256', $plain);
    }
}
