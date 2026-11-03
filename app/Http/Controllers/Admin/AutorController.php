<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutorRequest;
use App\Models\Autor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * R.F. 03 – registro de autores.
 */
class AutorController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('q', ''));

        return view('admin.cadastro-simples', [
            'titulo' => 'Autores',
            'singular' => 'autor',
            'campo' => 'nome_autor',
            'rotaBase' => 'admin.autores',
            'parametro' => 'autor',
            'busca' => $busca,
            'registros' => Autor::withCount('livros')
                ->when($busca !== '', fn ($q) => $q->where('nome_autor', 'like', "%{$busca}%"))
                ->orderBy('nome_autor')
                ->paginate((int) config('biblioteca.itens_por_pagina'))
                ->withQueryString(),
        ]);
    }

    public function store(AutorRequest $request): RedirectResponse
    {
        Autor::create($request->validated());

        return back()->with('status', 'Autor cadastrado.');
    }

    public function update(AutorRequest $request, Autor $autor): RedirectResponse
    {
        $autor->update($request->validated());

        return back()->with('status', 'Autor atualizado.');
    }

    /** RN11 – autor vinculado a livro não pode ser excluído. */
    public function destroy(Autor $autor): RedirectResponse
    {
        if ($autor->livros()->exists()) {
            return back()->withErrors(['regra' => "O autor \"{$autor->nome_autor}\" está vinculado a livros e não pode ser excluído."]);
        }

        $autor->delete();

        return back()->with('status', 'Autor excluído.');
    }
}
