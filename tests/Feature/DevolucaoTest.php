<?php

namespace Tests\Feature;

use App\Mail\AtrasoMail;
use App\Models\Emprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * R.F. 10 a 12 – devolução, contribuição e notificação (CT08, RN18 a RN22).
 */
class DevolucaoTest extends TestCase
{
    use RefreshDatabase;

    /** CT08 */
    public function test_devolucao_com_tres_dias_de_atraso_gera_contribuicao_de_tres_reais(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $livro = Livro::factory()->exemplares(2)->create();

        $this->travel(-17)->days(); // prazo de 14 dias -> 3 dias de atraso hoje
        $emprestimo = app(EmprestimoService::class)->registrar(Usuario::factory()->create(), [$livro->id_livro]);
        $this->travelBack();
        $this->assertSame(1, $livro->fresh()->quantidade_disponivel);

        $this->actingAs($admin)
            ->post(route('admin.emprestimos.devolver', $emprestimo), ['itens' => $emprestimo->itens->pluck('id_item')->all()])
            ->assertSessionHasNoErrors();

        $emprestimo->refresh();
        $this->assertSame(Emprestimo::DEVOLVIDO, $emprestimo->status);
        $this->assertSame('3.00', $emprestimo->valor_contribuicao);
        $this->assertSame(3, $emprestimo->itens->first()->dias_atraso);
        $this->assertSame(2, $livro->fresh()->quantidade_disponivel);
        $this->assertTrue($emprestimo->temContribuicaoPendente());
    }

    /** RN18 – devolução parcial mantém o empréstimo ativo */
    public function test_devolucao_parcial(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $emprestimo = app(EmprestimoService::class)->registrar(Usuario::factory()->create(), Livro::factory()->count(2)->create()->pluck('id_livro')->all());

        $this->actingAs($admin)->post(route('admin.emprestimos.devolver', $emprestimo), ['itens' => [$emprestimo->itens->first()->id_item]]);

        $emprestimo->refresh();
        $this->assertSame(Emprestimo::ATIVO, $emprestimo->status);
        $this->assertSame(1, $emprestimo->itens()->whereNull('data_devolucao')->count());
    }

    /** R.F. 11 – RN22 */
    public function test_pagamento_da_contribuicao_libera_novos_emprestimos(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $colab = Usuario::factory()->create();
        $servico = app(EmprestimoService::class);

        $this->travel(-16)->days();
        $emprestimo = $servico->registrar($colab, [Livro::factory()->create()->id_livro]);
        $this->travelBack();
        $servico->devolver($emprestimo, $emprestimo->itens->pluck('id_item')->all());

        $this->assertNotEmpty($servico->pendencias($colab));

        $this->actingAs($admin)->post(route('admin.emprestimos.pagamento', $emprestimo))->assertSessionHasNoErrors();

        $this->assertNotNull($emprestimo->fresh()->data_pagamento_contribuicao);
        $this->assertSame([], $servico->pendencias($colab));
    }

    /** R.F. 12 */
    public function test_notificacao_de_atraso_envia_email(): void
    {
        Mail::fake();
        $admin = Usuario::factory()->admin()->create();
        $colab = Usuario::factory()->create();

        $this->travel(-20)->days();
        app(EmprestimoService::class)->registrar($colab, [Livro::factory()->create()->id_livro]);
        $this->travelBack();

        $this->actingAs($admin)->post(route('admin.emprestimos.notificar-todos'))->assertSessionHasNoErrors();

        Mail::assertSent(AtrasoMail::class, fn (AtrasoMail $m) => $m->hasTo($colab->email));
    }
}
