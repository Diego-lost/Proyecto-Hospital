<?php

namespace Tests\Unit;

use App\Support\FrontendPublicUrl;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FrontendPublicUrlTest extends TestCase
{
    public function test_resuelve_frontend_en_estructura_xampp_tipica(): void
    {
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://localhost/ProyectoNuevo/backend/api/public');

        $this->assertSame(
            'http://localhost/ProyectoNuevo/frontend/',
            FrontendPublicUrl::resolve()
        );
    }

    public function test_respeta_frontend_url_en_env(): void
    {
        config(['app.frontend_url' => 'https://clinica.example/inicio']);

        $this->assertSame('https://clinica.example/inicio', FrontendPublicUrl::resolve());
    }

    public function test_usa_fallback_cuando_la_raiz_es_artisan_serve(): void
    {
        config(['app.frontend_url' => null]);
        config(['app.frontend_url_fallback' => 'http://ejemplo.test/clinica/index.html']);
        URL::forceRootUrl('http://127.0.0.1:8000');

        $this->assertSame('http://ejemplo.test/clinica/index.html', FrontendPublicUrl::resolve());
    }
}
