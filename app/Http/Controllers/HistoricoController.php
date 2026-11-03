<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * R.F. 16 – histórico de empréstimos do colaborador.
 */
class HistoricoController extends Controller
{
    /** Colaborador: apenas o próprio histórico. */
    public function meu(Request $request): View
    {
        return $this->historico($request->user(), false);
    }

    /** Administrador: histórico de qualquer colaborador. */
    public function usuario(Usuario $usuario): View
    {
        return $this->historico($usuario, true);
    }

    private function historico(Usuario $usuario, bool $visaoAdmin): View
    {
        $emprestimos = $usuario->emprestimos()
            ->with('itens.livro')
            ->latest('data_emprestimo')
            ->paginate((int) config('biblioteca.itens_por_pagina'));

        return view('historico.index', [
            'usuario' => $usuario,
            'emprestimos' => $emprestimos,
            'visaoAdmin' => $visaoAdmin,
            'totalLivros' => $usuario->emprestimos()->withCount('itens')->get()->sum('itens_count'),
            'totalContribuicoes' => (float) $usuario->emprestimos()->sum('valor_contribuicao'),
        ]);
    }
}
