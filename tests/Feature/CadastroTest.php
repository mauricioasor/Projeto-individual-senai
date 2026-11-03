<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R.F. 02 – autocadastro (CT03, RN02 e RN04).
 */
class CadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_colaborador_cria_conta_com_email_corporativo_e_perfil_usuario(): void
    {
        $this->post('/cadastro', [
            'nome' => 'Ana Souza',
            'email' => 'Ana.Souza@empresa.com.br',
            'senha' => 'Senha123',
            'senha_confirmation' => 'Senha123',
            'tipo_perfil' => 'ADMIN', // tentativa de escolher o perfil é ignorada
        ])->assertRedirect(route('login'));

        $usuario = Usuario::where('email', 'ana.souza@empresa.com.br')->first();
        $this->assertNotNull($usuario);
        $this->assertSame(Usuario::USUARIO, $usuario->tipo_perfil);
    }

    /** CT03 */
    public function test_email_fora_do_dominio_e_recusado(): void
    {
        $this->post('/cadastro', [
            'nome' => 'Fulano de Tal',
            'email' => 'fulano@gmail.com',
            'senha' => 'Senha123',
            'senha_confirmation' => 'Senha123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('USUARIO', ['email' => 'fulano@gmail.com']);
    }

    public function test_email_repetido_e_recusado(): void
    {
        Usuario::factory()->create(['email' => 'ana@empresa.com.br']);

        $this->post('/cadastro', [
            'nome' => 'Ana Outra',
            'email' => 'ANA@empresa.com.br',
            'senha' => 'Senha123',
            'senha_confirmation' => 'Senha123',
        ])->assertSessionHasErrors('email');
    }
}
