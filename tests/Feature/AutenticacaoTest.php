<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R.F. 06 – autenticação (CT01 e CT02) e RN01.
 */
class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_sucesso_direciona_para_o_painel_do_perfil(): void
    {
        $admin = Usuario::factory()->admin()->create(['email' => 'admin@empresa.com.br']);
        $colab = Usuario::factory()->create(['email' => 'colab@empresa.com.br']);

        $this->post('/login', ['email' => 'admin@empresa.com.br', 'senha' => 'Senha@123'])
            ->assertRedirect(route('painel'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('painel'))->assertRedirect(route('admin.painel'));

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', ['email' => 'colab@empresa.com.br', 'senha' => 'Senha@123']);
        $this->assertAuthenticatedAs($colab);
        $this->get(route('painel'))->assertRedirect(route('painel.colaborador'));
        $this->get(route('painel.colaborador'))->assertOk();
    }

    /** CT01 */
    public function test_senha_incorreta_exibe_mensagem_generica(): void
    {
        Usuario::factory()->create(['email' => 'colab@empresa.com.br']);

        $this->from('/login')->post('/login', ['email' => 'colab@empresa.com.br', 'senha' => 'errada123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);

        // E-mail inexistente recebe a mesma mensagem (R.N.F. 10)
        $this->from('/login')->post('/login', ['email' => 'naoexiste@empresa.com.br', 'senha' => 'errada123'])
            ->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);

        $this->assertGuest();
    }

    /** CT02 – RN06 */
    public function test_cinco_tentativas_invalidas_bloqueiam_a_conta_por_15_minutos(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'colab@empresa.com.br']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'colab@empresa.com.br', 'senha' => 'errada123']);
        }

        $usuario->refresh();
        $this->assertNotNull($usuario->bloqueado_ate);
        $this->assertTrue($usuario->estaBloqueado());

        // Mesmo com a senha correta, o acesso fica bloqueado
        $this->post('/login', ['email' => 'colab@empresa.com.br', 'senha' => 'Senha@123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Após 15 minutos, volta a entrar
        $this->travel(16)->minutes();
        $this->post('/login', ['email' => 'colab@empresa.com.br', 'senha' => 'Senha@123']);
        $this->assertAuthenticatedAs($usuario);
    }

    /** RN01 */
    public function test_usuario_inativo_nao_entra(): void
    {
        Usuario::factory()->inativo()->create(['email' => 'inativo@empresa.com.br']);

        $this->post('/login', ['email' => 'inativo@empresa.com.br', 'senha' => 'Senha@123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_paginas_internas_exigem_login(): void
    {
        $this->get(route('catalogo'))->assertRedirect(route('login'));
        $this->get(route('admin.painel'))->assertRedirect(route('login'));
    }

    /** R.N.F. 03 – senha armazenada como hash bcrypt */
    public function test_senha_e_armazenada_como_hash(): void
    {
        $usuario = Usuario::factory()->create(['senha' => 'MinhaSenha1']);

        $this->assertNotSame('MinhaSenha1', $usuario->getRawOriginal('senha'));
        $this->assertStringStartsWith('$2y$', $usuario->getRawOriginal('senha'));
    }
}
