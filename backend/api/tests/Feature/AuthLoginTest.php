<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login_desde_admin(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_puede_iniciar_sesion_y_entrar_al_panel(): void
    {
        $response = $this->post(route('login'), [
            'email' => config('dev_login.email'),
            'password' => config('dev_login.password'),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->devPanelUser(), 'web');
    }

    public function test_credenciales_incorrectas_vuelven_al_login(): void
    {
        $this->post(route('login'), [
            'email' => config('dev_login.email'),
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_autenticado_es_redirigido_si_visita_login(): void
    {
        $this->actingAs($this->devPanelUser())
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_cierra_sesion(): void
    {
        $this->actingAs($this->devPanelUser())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
