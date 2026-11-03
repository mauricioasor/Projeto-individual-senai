<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * R.F. 15 – consulta ao catálogo, com filtros e paginação.
 * O Administrador vê, na mesma tela, as ações de manutenção do acervo (R.F. 05).
 */
class CatalogoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'disponibilidade' => ['nullable', 'in:disponivel,indisponivel'],
        ]);

        $busca = trim($filtros['q'] ?? '');

        $livros = Livro::query()
            ->with(['categoria', 'autores'])
            // Eloquent usa parâmetros vinculados (R.N.F. 08)
            ->when($busca !== '', function ($q) use ($busca) {
                // Busca por ISBN só quando o texto parece um ISBN (números, hífens e X)
                $isbn = preg_match('/^[\d\-\sXx]+$/', $busca) ? preg_replace('/[^0-9Xx]/', '', $busca) : '';
                $q->where(function ($q) use ($busca, $isbn) {
                    $q->where('titulo', 'like', "%{$busca}%")
                        ->orWhereHas('autores', fn ($a) => $a->where('nome_autor', 'like', "%{$busca}%"));
                    if ($isbn !== '') {
                        $q->orWhere('isbn', 'like', "%{$isbn}%");
                    }
                });
            })
            ->when($filtros['categoria'] ?? null, fn ($q, $id) => $q->where('id_categoria', $id))
            ->when(($filtros['disponibilidade'] ?? null) === 'disponivel', fn ($q) => $q->where('quantidade_disponivel', '>', 0))
            ->when(($filtros['disponibilidade'] ?? null) === 'indisponivel', fn ($q) => $q->where('quantidade_disponivel', 0))
            ->orderBy('titulo')
            ->paginate((int) config('biblioteca.itens_por_pagina'))
            ->withQueryString();

        return view('catalogo.index', [
            'livros' => $livros,
            'categorias' => Categoria::orderBy('nome_categoria')->get(),
            'filtros' => $filtros,
            'totalTitulos' => Livro::count(),
            'totalExemplares' => (int) Livro::sum('quantidade_total'),
        ]);
    }
}
