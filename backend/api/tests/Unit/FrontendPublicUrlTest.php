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
            'http://localhost/ProyectoNuevo/frontend/index.html',
            FrontendPublicUrl::resolve()
        );
    }

    public function test_respeta_frontend_url_en_env(): void
    {
        config(['app.frontend_url' => 'https://clinica.example/inicio']);

        $this->assertSame('https://clinica.example/inicio', FrontendPublicUrl::resolve());
    }
}
