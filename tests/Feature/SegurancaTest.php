<?php

namespace Tests\Feature;

use App\Models\Livro;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Requisitos não funcionais de segurança (CT10, CT11 e CT13).
 */
class SegurancaTest extends TestCase
{
    use RefreshDatabase;

    /** CT10 – R.N.F. 06 */
    public function test_colaborador_nao_acessa_rotas_do_administrador(): void
    {
        $colab = Usuario::factory()->create();

        $this->actingAs($colab);
        $this->get(route('admin.painel'))->assertForbidden();
        $this->get(route('admin.livros.create'))->assertForbidden();
        $this->get(route('admin.usuarios.index'))->assertForbidden();
        $this->get(route('admin.relatorios.emprestimos'))->assertForbidden();
        $this->post(route('admin.autores.store'), ['nome_autor' => 'Invasor'])->assertForbidden();
        $this->assertDatabaseMissing('AUTOR', ['nome_autor' => 'Invasor']);
    }

    /** CT11 – R.N.F. 08 */
    public function test_busca_com_injecao_de_sql_e_tratada_como_texto(): void
    {
        Livro::factory()->count(3)->create();

        $this->actingAs(Usuario::factory()->create())
            ->get(route('catalogo', ['q' => "' OR 1=1 --"]))
            ->assertOk()
            ->assertSee('Nenhum livro encontrado');
    }

    /** R.N.F. 09 – XSS */
    public function test_conteudo_html_e_escapado_nas_telas(): void
    {
        Livro::factory()->create(['titulo' => '<script>alert(1)</script>']);

        $this->actingAs(Usuario::factory()->create())
            ->get(route('catalogo'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    /** CT13 – R.N.F. 05 */
    public function test_formulario_sem_token_csrf_e_recusado(): void
    {
        $admin = Usuario::factory()->admin()->create();

        // Nos testes o Laravel ignora o CSRF; simulamos o ambiente real
        $this->app['env'] = 'production';

        $this->actingAs($admin)
            ->post(route('admin.autores.store'), ['nome_autor' => 'Sem token'])
            ->assertStatus(419);

        $this->assertDatabaseMissing('AUTOR', ['nome_autor' => 'Sem token']);
    }

    /** CT13 – formulário enviado por outro site (ataque CSRF) */
    public function test_requisicao_de_outro_site_sem_token_e_recusada(): void
    {
        $this->app['env'] = 'production';

        $this->withHeaders(['Sec-Fetch-Site' => 'cross-site', 'Origin' => 'http://site-malicioso.test'])
            ->post(route('cadastro.store'), [
                'nome' => 'Conta Forjada',
                'email' => 'vitima@empresa.com.br',
                'senha' => 'Senha@123',
                'senha_confirmation' => 'Senha@123',
            ])
            ->assertStatus(419);

        $this->assertDatabaseMissing('USUARIO', ['email' => 'vitima@empresa.com.br']);
    }

    /** RN01 – conta inativada perde o acesso na próxima requisição */
    public function test_usuario_inativado_e_desconectado(): void
    {
        $colab = Usuario::factory()->create();
        $this->actingAs($colab);
        $colab->update(['situacao' => Usuario::INATIVO]);

        $this->get(route('catalogo'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
