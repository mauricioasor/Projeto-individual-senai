<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Livro;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R.F. 03 a 05 – cadastros do acervo (CT04, RN09 a RN11).
 */
class LivroTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Usuario
    {
        return Usuario::factory()->admin()->create();
    }

    public function test_admin_cadastra_livro_com_varios_autores(): void
    {
        $categoria = Categoria::factory()->create();
        $autores = Autor::factory()->count(2)->create();

        $this->actingAs($this->admin())->post(route('admin.livros.store'), [
            'titulo' => 'Arquitetura limpa',
            'isbn' => '978-85-02-00054-9',
            'ano_publicacao' => 2024,
            'quantidade_total' => 4,
            'id_categoria' => $categoria->id_categoria,
            'autores' => $autores->pluck('id_autor')->all(),
        ])->assertRedirect(route('catalogo'));

        $livro = Livro::where('isbn', '9788502000549')->firstOrFail();
        $this->assertSame(4, $livro->quantidade_disponivel);
        $this->assertCount(2, $livro->autores);
    }

    /** CT04 */
    public function test_isbn_repetido_e_recusado(): void
    {
        $livro = Livro::factory()->create(['isbn' => '9788502000549']);

        $this->actingAs($this->admin())->post(route('admin.livros.store'), [
            'titulo' => 'Outro título',
            'isbn' => '9788502000549',
            'quantidade_total' => 1,
            'id_categoria' => $livro->id_categoria,
            'autores' => [Autor::factory()->create()->id_autor],
        ])->assertSessionHasErrors('isbn');

        $this->assertSame(1, Livro::count());
    }

    public function test_livro_exige_pelo_menos_um_autor(): void
    {
        $this->actingAs($this->admin())->post(route('admin.livros.store'), [
            'titulo' => 'Sem autor',
            'isbn' => '9788502000778',
            'quantidade_total' => 1,
            'id_categoria' => Categoria::factory()->create()->id_categoria,
            'autores' => [],
        ])->assertSessionHasErrors('autores');
    }

    /** RN10 – total não pode ficar abaixo dos exemplares emprestados */
    public function test_reduzir_total_abaixo_dos_emprestados_e_recusado(): void
    {
        $livro = Livro::factory()->exemplares(3, 1)->create(); // 2 emprestados

        $this->actingAs($this->admin())->put(route('admin.livros.update', $livro), [
            'titulo' => $livro->titulo,
            'isbn' => $livro->isbn,
            'quantidade_total' => 1,
            'id_categoria' => $livro->id_categoria,
            'autores' => $livro->autores->pluck('id_autor')->all(),
        ])->assertSessionHasErrors('quantidade_total');

        $this->assertSame(3, $livro->fresh()->quantidade_total);
    }

    /** RN11 */
    public function test_autor_vinculado_a_livro_nao_pode_ser_excluido(): void
    {
        $livro = Livro::factory()->create();
        $autor = $livro->autores->first();

        $this->actingAs($this->admin())->delete(route('admin.autores.destroy', $autor))->assertSessionHasErrors('regra');
        $this->assertDatabaseHas('AUTOR', ['id_autor' => $autor->id_autor]);
    }

    public function test_catalogo_filtra_por_titulo_e_disponibilidade(): void
    {
        Livro::factory()->create(['titulo' => 'Gestão ágil na prática']);
        Livro::factory()->exemplares(2, 0)->create(['titulo' => 'Livro esgotado']);

        $this->actingAs(Usuario::factory()->create())
            ->get(route('catalogo', ['q' => 'ágil']))
            ->assertOk()->assertSee('Gestão ágil na prática')->assertDontSee('Livro esgotado');

        $this->get(route('catalogo', ['disponibilidade' => 'indisponivel']))
            ->assertSee('Livro esgotado')->assertDontSee('Gestão ágil na prática');
    }
}
