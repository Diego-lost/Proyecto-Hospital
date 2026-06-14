<?php

namespace App\Support;

use App\Models\User;

final class AuthRedirect
{
    public static function forUser(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        return FrontendPublicUrl::resolve();
    }

    /**
     * @return array{id: int, name: string, email: string, role: string}
     */
    public static function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
