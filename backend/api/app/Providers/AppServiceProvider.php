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
    }
}
