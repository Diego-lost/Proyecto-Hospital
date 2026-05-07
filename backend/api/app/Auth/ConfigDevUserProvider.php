<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Usuario fijo definido en config/dev_login.php (no persiste en tabla users).
 */
final class ConfigDevUserProvider implements UserProvider
{
    private const DEV_USER_ID = 1;

    public function retrieveById($identifier): ?Authenticatable
    {
        if ((string) $identifier !== (string) self::DEV_USER_ID) {
            return null;
        }

        return $this->genericUser();
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        //
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (($credentials['email'] ?? null) !== config('dev_login.email')) {
            return null;
        }

        return $this->genericUser();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        return hash_equals(
            (string) config('dev_login.password'),
            (string) $credentials['password']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        //
    }

    private function genericUser(): GenericUser
    {
        return new GenericUser([
            'id' => self::DEV_USER_ID,
            'name' => config('dev_login.name'),
            'email' => config('dev_login.email'),
            'password' => '',
            'remember_token' => '',
        ]);
    }
}
