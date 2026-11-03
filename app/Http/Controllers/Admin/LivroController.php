<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LivroRequest;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Livro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * R.F. 05 – registro de livros (RN08 a RN11).
 * A listagem é a própria tela de catálogo (R.F. 15), com as ações do Administrador.
 */
class LivroController extends Controller
{
    public function create(): View
    {
        return view('admin.livros.form', $this->dadosFormulario(new Livro()));
    }

    public function store(LivroRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $dados = $request->safe()->except('autores');
            // No cadastro, a quantidade disponível é igual à total
            $dados['quantidade_disponivel'] = $dados['quantidade_total'];

            $livro = Livro::create($dados);
            $livro->autores()->sync($request->validated('autores'));
        });

        return redirect()->route('catalogo')->with('status', 'Livro cadastrado com sucesso.');
    }

    public function edit(Livro $livro): View
    {
        return view('admin.livros.form', $this->dadosFormulario($livro->load('autores')));
    }

    public function update(LivroRequest $request, Livro $livro): RedirectResponse
    {
        $resultado = DB::transaction(function () use ($request, $livro) {
            $livro = Livro::whereKey($livro->getKey())->lockForUpdate()->firstOrFail();
            $dados = $request->safe()->except('autores');

            // RN10 – a disponível acompanha a variação da total e nunca fica negativa
            $emprestados = $livro->quantidade_total - $livro->quantidade_disponivel;
            if ($dados['quantidade_total'] < $emprestados) {
                return "Há {$emprestados} exemplar(es) emprestado(s); a quantidade total não pode ser menor que isso.";
            }
            $dados['quantidade_disponivel'] = $dados['quantidade_total'] - $emprestados;

            $livro->update($dados);
            $livro->autores()->sync($request->validated('autores'));

            return true;
        });

        if ($resultado !== true) {
            return back()->withErrors(['quantidade_total' => $resultado])->withInput();
        }

        return redirect()->route('catalogo')->with('status', 'Livro atualizado com sucesso.');
    }

    /** RN11 – livro com histórico de empréstimos não pode ser excluído. */
    public function destroy(Livro $livro): RedirectResponse
    {
        if ($livro->itens()->whereNull('data_devolucao')->exists()) {
            return back()->withErrors(['regra' => "O livro \"{$livro->titulo}\" tem exemplares emprestados e não pode ser excluído."]);
        }
        if ($livro->itens()->exists()) {
            return back()->withErrors(['regra' => "O livro \"{$livro->titulo}\" possui histórico de empréstimos e não pode ser excluído."]);
        }

        $livro->delete();

        return redirect()->route('catalogo')->with('status', 'Livro excluído.');
    }

    private function dadosFormulario(Livro $livro): array
    {
        return [
            'livro' => $livro,
            'categorias' => Categoria::orderBy('nome_categoria')->get(),
            'autores' => Autor::orderBy('nome_autor')->get(),
        ];
    }
}
