<?php

namespace Tests\Feature;

use App\Mail\RecuperacaoSenhaMail;
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * R.F. 07 – recuperação de senha por e-mail (CT09 e RN07).
 */
class RecuperacaoSenhaTest extends TestCase
{
    use RefreshDatabase;

    private function solicitarLink(Usuario $usuario): string
    {
        $link = null;
        Mail::fake();
        $this->post(route('senha.enviar'), ['email' => $usuario->email]);
        Mail::assertSent(RecuperacaoSenhaMail::class, function (RecuperacaoSenhaMail $m) use (&$link) {
            $link = $m->link;

            return true;
        });

        return basename(parse_url($link, PHP_URL_PATH));
    }

    public function test_fluxo_completo_de_redefinicao(): void
    {
        $usuario = Usuario::factory()->create();
        $token = $this->solicitarLink($usuario);

        // Apenas o hash do token fica no banco
        $this->assertDatabaseHas('RECUPERACAO_SENHA', ['token_hash' => hash('sha256', $token), 'utilizado' => false]);
        $this->assertDatabaseMissing('RECUPERACAO_SENHA', ['token_hash' => $token]);

        $this->get(route('senha.redefinir', $token))->assertOk()->assertSee('Salvar nova senha');

        $this->post(route('senha.atualizar'), ['token' => $token, 'senha' => 'NovaSenha9', 'senha_confirmation' => 'NovaSenha9'])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NovaSenha9', $usuario->fresh()->senha));
    }

    /** CT09 – link de uso único */
    public function test_link_nao_pode_ser_usado_duas_vezes(): void
    {
        $usuario = Usuario::factory()->create();
        $token = $this->solicitarLink($usuario);

        $this->post(route('senha.atualizar'), ['token' => $token, 'senha' => 'NovaSenha9', 'senha_confirmation' => 'NovaSenha9']);
        $this->post(route('senha.atualizar'), ['token' => $token, 'senha' => 'OutraSenha9', 'senha_confirmation' => 'OutraSenha9'])
            ->assertRedirect(route('senha.solicitar'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('NovaSenha9', $usuario->fresh()->senha));
    }

    /** CT09 – link expira em 30 minutos */
    public function test_link_expira_em_30_minutos(): void
    {
        $usuario = Usuario::factory()->create();
        $token = $this->solicitarLink($usuario);

        $this->travel(31)->minutes();

        $this->get(route('senha.redefinir', $token))->assertSee('inválido');
        $this->post(route('senha.atualizar'), ['token' => $token, 'senha' => 'NovaSenha9', 'senha_confirmation' => 'NovaSenha9'])
            ->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('NovaSenha9', $usuario->fresh()->senha));
    }

    /** R.N.F. 10 – mesma resposta para e-mail inexistente */
    public function test_email_inexistente_recebe_a_mesma_mensagem(): void
    {
        Mail::fake();

        $this->post(route('senha.enviar'), ['email' => 'ninguem@empresa.com.br'])
            ->assertSessionHas('status');

        Mail::assertNothingSent();
        $this->assertSame(0, RecuperacaoSenha::count());
    }
}
