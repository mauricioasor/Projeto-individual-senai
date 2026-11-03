<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * R.F. 04 – registro de categorias.
 */
class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('q', ''));

        return view('admin.cadastro-simples', [
            'titulo' => 'Categorias',
            'singular' => 'categoria',
            'campo' => 'nome_categoria',
            'rotaBase' => 'admin.categorias',
            'parametro' => 'categoria',
            'busca' => $busca,
            'registros' => Categoria::withCount('livros')
                ->when($busca !== '', fn ($q) => $q->where('nome_categoria', 'like', "%{$busca}%"))
                ->orderBy('nome_categoria')
                ->paginate((int) config('biblioteca.itens_por_pagina'))
                ->withQueryString(),
        ]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return back()->with('status', 'Categoria cadastrada.');
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return back()->with('status', 'Categoria atualizada.');
    }

    /** RN11 – categoria vinculada a livro não pode ser excluída. */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->livros()->exists()) {
            return back()->withErrors(['regra' => "A categoria \"{$categoria->nome_categoria}\" está vinculada a livros e não pode ser excluída."]);
        }

        $categoria->delete();

        return back()->with('status', 'Categoria excluída.');
    }
}
