<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_admin_puede_iniciar_sesion_y_entrar_al_panel(): void
    {
        $admin = User::factory()->admin()->verified()->create([
            'email' => 'admin@local.test',
            'password' => 'password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_usuario_puede_iniciar_sesion_y_es_redirigido_al_sitio_web(): void
    {
        $user = User::factory()->user()->verified()->create([
            'email' => 'paciente@local.test',
            'password' => 'password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertStringNotContainsString('/admin', $response->headers->get('Location') ?? '');
    }

    public function test_credenciales_incorrectas_vuelven_al_login(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@local.test',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => 'admin@local.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_autenticado_es_redirigido_si_visita_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_usuario_no_admin_no_puede_entrar_al_panel(): void
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect();
    }

    public function test_logout_cierra_sesion(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
