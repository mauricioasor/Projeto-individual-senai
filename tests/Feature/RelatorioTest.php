<?php

namespace Tests\Feature;

use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R.F. 13 e R.F. 17 – painel e relatório com exportação (CT12).
 */
class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Usuario::factory()->admin()->create();
        $colab = Usuario::factory()->create(['nome' => 'Joana Mendonça']);
        $livro = Livro::factory()->create(['titulo' => 'Introdução à programação']);
        app(EmprestimoService::class)->registrar($colab, [$livro->id_livro]);
    }

    public function test_painel_do_administrador_exibe_indicadores(): void
    {
        $this->actingAs($this->admin)->get(route('admin.painel'))
            ->assertOk()
            ->assertSee('Empréstimos ativos')
            ->assertSee('Joana Mendonça');
    }

    public function test_relatorio_lista_emprestimos_do_periodo(): void
    {
        $this->actingAs($this->admin)->get(route('admin.relatorios.emprestimos'))
            ->assertOk()
            ->assertSee('Introdução à programação');
    }

    /** CT12 */
    public function test_exportacao_csv_com_acentuacao(): void
    {
        $resposta = $this->actingAs($this->admin)->get(route('admin.relatorios.exportar', ['formato' => 'csv']));

        $resposta->assertOk();
        $this->assertStringContainsString('text/csv', $resposta->headers->get('Content-Type'));
        $conteudo = $resposta->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $conteudo);
        $this->assertStringContainsString('Introdução à programação', $conteudo);
        $this->assertStringContainsString('Joana Mendonça', $conteudo);
    }

    public function test_relatorio_calcula_dias_de_atraso_dos_livros_em_posse(): void
    {
        $this->travel(17)->days();

        $conteudo = $this->actingAs($this->admin)
            ->get(route('admin.relatorios.exportar', ['formato' => 'csv', 'inicio' => now()->subDays(30)->toDateString()]))
            ->streamedContent();

        $this->assertStringContainsString(';3;0,00;Atrasado', $conteudo);
    }

    public function test_exportacao_pdf(): void
    {
        $resposta = $this->actingAs($this->admin)->get(route('admin.relatorios.exportar', ['formato' => 'pdf']));

        $resposta->assertOk();
        $this->assertSame('application/pdf', $resposta->headers->get('Content-Type'));
    }
}
