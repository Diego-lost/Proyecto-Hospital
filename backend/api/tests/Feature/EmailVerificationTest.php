<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_verificado_no_puede_iniciar_sesion(): void
    {
        User::factory()->unverified()->user()->create([
            'email' => 'sin-verificar@local.test',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => 'sin-verificar@local.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_enlace_firmado_verifica_correo_e_inicia_sesion(): void
    {
        $user = User::factory()->unverified()->user()->create([
            'email' => 'verificar@local.test',
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->get($url)->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }
}
