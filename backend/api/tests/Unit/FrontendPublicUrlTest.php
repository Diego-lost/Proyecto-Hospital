<?php

namespace Tests\Unit;

use App\Support\FrontendPublicUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FrontendPublicUrlTest extends TestCase
{
    public function test_resuelve_frontend_en_estructura_xampp_tipica(): void
    {
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://localhost/ProyectoNuevo/backend/api/public');

        $this->assertSame(
            'http://localhost/ProyectoNuevo/backend/api/public/clinica/',
            FrontendPublicUrl::resolve()
        );
    }

    public function test_respeta_frontend_url_en_env(): void
    {
        config(['app.frontend_url' => 'https://clinica.example/inicio']);

        $this->assertSame('https://clinica.example/inicio', FrontendPublicUrl::resolve());
    }

    public function test_artisan_serve_apunta_a_clinica_sincronizado_o_vite(): void
    {
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://127.0.0.1:8000');

        $this->assertSame(
            self::expectedArtisanServeFrontUrl('http://127.0.0.1:8000'),
            FrontendPublicUrl::resolve()
        );
    }

    public function test_artisan_serve_localhost_mismo_host_que_clinica_o_vite(): void
    {
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://localhost:8000');

        $this->assertSame(
            self::expectedArtisanServeFrontUrl('http://localhost:8000'),
            FrontendPublicUrl::resolve()
        );
    }

    public function test_app_url_sin_puerto_usa_host_de_la_peticion_para_clinica_o_vite(): void
    {
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://localhost');

        $this->app->instance('request', Request::create('http://127.0.0.1:8000/admin', 'GET'));

        $this->assertSame(
            self::expectedArtisanServeFrontUrl('http://127.0.0.1:8000'),
            FrontendPublicUrl::resolve()
        );
    }

    private static function expectedArtisanServeFrontUrl(string $root): string
    {
        $root = rtrim($root, '/');
        $subdir = trim((string) config('frontend_sync.target_subdir', 'clinica'), '/');
        if ($subdir !== '' && is_file(public_path($subdir.'/index.html'))) {
            return $root.'/'.$subdir.'/';
        }

        $parts = parse_url($root);
        if (! is_array($parts)) {
            return 'http://127.0.0.1:5173/';
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '127.0.0.1';

        return $scheme.'://'.$host.':5173/';
    }
}
