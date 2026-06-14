<?php

namespace App\Providers;

use App\Auth\ConfigDevUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('config_dev', fn ($app, array $config): ConfigDevUserProvider => new ConfigDevUserProvider);

        $this->configureCrossOriginFrontend();
    }

    /**
     * Front en Firebase/Vercel y API en otro dominio (Render): CORS + cookies SameSite=None.
     */
    private function configureCrossOriginFrontend(): void
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($frontend === '' || $appUrl === '') {
            return;
        }

        $frontendHost = parse_url($frontend, PHP_URL_HOST);
        $appHost = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($frontendHost) || ! is_string($appHost) || strcasecmp($frontendHost, $appHost) === 0) {
            return;
        }

        $allowedOrigins = env('CORS_ALLOWED_ORIGINS', $frontend);
        $origins = array_values(array_filter(array_map('trim', explode(',', (string) $allowedOrigins))));

        config([
            'session.same_site' => env('SESSION_SAME_SITE', 'none'),
            'session.secure' => filter_var(env('SESSION_SECURE_COOKIE', true), FILTER_VALIDATE_BOOL),
            'cors.allowed_origins' => $origins !== [] ? $origins : [$frontend],
            'cors.supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', true), FILTER_VALIDATE_BOOL),
        ]);
    }
}
