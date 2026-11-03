<?php

namespace Tests\Feature;

use App\Models\Emprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R.F. 08 e 09 – empréstimo mestre-detalhe e renovação (CT05 a CT07, RN12 a RN17).
 */
class EmprestimoTest extends TestCase
{
    use RefreshDatabase;

    public function test_colaborador_registra_emprestimo_com_dois_livros(): void
    {
        $colab = Usuario::factory()->create();
        [$a, $b] = Livro::factory()->count(2)->create()->all();

        $this->actingAs($colab)->post(route('emprestimos.store'), ['livros' => [$a->id_livro, $b->id_livro, '']])
            ->assertRedirect(route('painel.colaborador'))
            ->assertSessionHasNoErrors();

        $emprestimo = Emprestimo::firstOrFail();
        $this->assertSame($colab->id_usuario, $emprestimo->id_usuario);
        $this->assertCount(2, $emprestimo->itens);
        $this->assertTrue($emprestimo->data_prevista_dev->isSameDay(today()->addDays(14)));
        $this->assertSame(2, $a->fresh()->quantidade_disponivel);
    }

    public function test_colaborador_nao_registra_emprestimo_para_outra_pessoa(): void
    {
        $colab = Usuario::factory()->create();
        $outro = Usuario::factory()->create();
        $livro = Livro::factory()->create();

        $this->actingAs($colab)->post(route('emprestimos.store'), ['id_usuario' => $outro->id_usuario, 'livros' => [$livro->id_livro]]);

        $this->assertSame($colab->id_usuario, Emprestimo::firstOrFail()->id_usuario);
    }

    public function test_admin_registra_emprestimo_para_colaborador(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $colab = Usuario::factory()->create();
        $livro = Livro::factory()->create();

        $this->actingAs($admin)->post(route('emprestimos.store'), ['id_usuario' => $colab->id_usuario, 'livros' => [$livro->id_livro]])
            ->assertSessionHasNoErrors();

        $this->assertSame($colab->id_usuario, Emprestimo::firstOrFail()->id_usuario);
    }

    /** CT05 – RN12 */
    public function test_livro_sem_exemplar_disponivel_nao_e_emprestado(): void
    {
        $livro = Livro::factory()->exemplares(2, 0)->create();

        $this->actingAs(Usuario::factory()->create())
            ->post(route('emprestimos.store'), ['livros' => [$livro->id_livro]])
            ->assertSessionHasErrors('regra');

        $this->assertSame(0, Emprestimo::count());
    }

    /** CT06 – RN14 */
    public function test_limite_de_tres_livros_em_posse(): void
    {
        $colab = Usuario::factory()->create();
        $livros = Livro::factory()->count(4)->create();
        app(EmprestimoService::class)->registrar($colab, $livros->take(3)->pluck('id_livro')->all());

        $this->actingAs($colab)->post(route('emprestimos.store'), ['livros' => [$livros[3]->id_livro]])
            ->assertSessionHasErrors('regra');

        $this->assertSame(1, Emprestimo::count());
    }

    /** RN13 */
    public function test_mesmo_livro_nao_pode_se_repetir_no_emprestimo(): void
    {
        $livro = Livro::factory()->create();

        $this->actingAs(Usuario::factory()->create())
            ->post(route('emprestimos.store'), ['livros' => [$livro->id_livro, $livro->id_livro]])
            ->assertSessionHasErrors('livros.0');
    }

    /** CT07 – RN17 */
    public function test_colaborador_com_atraso_nao_faz_novo_emprestimo(): void
    {
        $colab = Usuario::factory()->create();
        [$a, $b] = Livro::factory()->count(2)->create()->all();

        $this->travel(-20)->days();
        app(EmprestimoService::class)->registrar($colab, [$a->id_livro]);
        $this->travelBack();

        $this->actingAs($colab)->post(route('emprestimos.store'), ['livros' => [$b->id_livro]])
            ->assertSessionHasErrors('regra');

        $this->assertSame(1, Emprestimo::count());
    }

    /** R.F. 09 – RN16 */
    public function test_renovacao_unica_por_mais_14_dias(): void
    {
        $colab = Usuario::factory()->create();
        $emprestimo = app(EmprestimoService::class)->registrar($colab, [Livro::factory()->create()->id_livro]);
        $prevista = $emprestimo->data_prevista_dev->copy();

        $this->actingAs($colab)->post(route('emprestimos.renovar', $emprestimo))->assertSessionHasNoErrors();
        $this->assertTrue($emprestimo->fresh()->data_prevista_dev->isSameDay($prevista->addDays(14)));

        $this->post(route('emprestimos.renovar', $emprestimo))->assertSessionHasErrors('regra');
        $this->assertSame(1, $emprestimo->fresh()->qtd_renovacoes);
    }

    public function test_colaborador_nao_renova_emprestimo_de_outra_pessoa(): void
    {
        $dono = Usuario::factory()->create();
        $emprestimo = app(EmprestimoService::class)->registrar($dono, [Livro::factory()->create()->id_livro]);

        $this->actingAs(Usuario::factory()->create())
            ->post(route('emprestimos.renovar', $emprestimo))
            ->assertForbidden();
    }
}
