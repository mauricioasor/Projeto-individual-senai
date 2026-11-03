<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RegraNegocioException;
use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use App\Services\EmprestimoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestão de empréstimos pelo Administrador: listagem, detalhe,
 * devolução (R.F. 10) e pagamento da contribuição (R.F. 11).
 */
class EmprestimoAdminController extends Controller
{
    public function __construct(private EmprestimoService $servico)
    {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'aba' => ['nullable', 'in:todos,ativos,atrasados,devolvidos,pendentes'],
        ]);
        $aba = $filtros['aba'] ?? 'todos';
        $busca = trim($filtros['q'] ?? '');

        $emprestimos = Emprestimo::query()
            ->with(['usuario', 'itens.livro'])
            ->when($aba === 'ativos', fn ($q) => $q->emDia())
            ->when($aba === 'atrasados', fn ($q) => $q->atrasados())
            ->when($aba === 'devolvidos', fn ($q) => $q->where('status', Emprestimo::DEVOLVIDO))
            ->when($aba === 'pendentes', fn ($q) => $q->comContribuicaoPendente())
            ->when($busca !== '', fn ($q) => $q->where(function ($q) use ($busca) {
                $q->whereHas('usuario', fn ($u) => $u->where('nome', 'like', "%{$busca}%")->orWhere('email', 'like', "%{$busca}%"))
                    ->orWhereHas('itens.livro', fn ($l) => $l->where('titulo', 'like', "%{$busca}%"));
                if (ctype_digit(ltrim($busca, '#'))) {
                    $q->orWhere('id_emprestimo', (int) ltrim($busca, '#'));
                }
            }))
            ->orderByRaw("CASE WHEN status = 'ATIVO' THEN 0 ELSE 1 END")
            ->orderBy('data_prevista_dev')
            ->orderByDesc('data_emprestimo')
            ->paginate((int) config('biblioteca.itens_por_pagina'))
            ->withQueryString();

        return view('admin.emprestimos.index', [
            'emprestimos' => $emprestimos,
            'aba' => $aba,
            'busca' => $busca,
            'contagens' => [
                'todos' => Emprestimo::count(),
                'ativos' => Emprestimo::emDia()->count(),
                'atrasados' => Emprestimo::atrasados()->count(),
                'devolvidos' => Emprestimo::where('status', Emprestimo::DEVOLVIDO)->count(),
                'pendentes' => Emprestimo::comContribuicaoPendente()->count(),
            ],
        ]);
    }

    public function show(Emprestimo $emprestimo): View
    {
        $emprestimo->load(['usuario', 'itens.livro.autores']);

        return view('admin.emprestimos.show', [
            'emprestimo' => $emprestimo,
            'valorDia' => (float) config('biblioteca.valor_dia_atraso'),
            'maxRenovacoes' => (int) config('biblioteca.max_renovacoes'),
        ]);
    }

    public function devolver(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        $dados = $request->validate([
            'itens' => ['required', 'array', 'min:1'],
            'itens.*' => ['integer'],
        ], ['itens.required' => 'Selecione ao menos um livro para devolver.']);

        try {
            $emprestimo = $this->servico->devolver($emprestimo, $dados['itens']);
        } catch (RegraNegocioException $e) {
            return back()->withErrors(['regra' => $e->getMessage()]);
        }

        $mensagem = 'Devolução registrada.';
        if ((float) $emprestimo->valor_contribuicao > 0) {
            $mensagem .= ' Contribuição por atraso: R$ '.number_format((float) $emprestimo->valor_contribuicao, 2, ',', '.').'.';
        }

        return back()->with('status', $mensagem);
    }

    public function pagamento(Emprestimo $emprestimo): RedirectResponse
    {
        try {
            $this->servico->registrarPagamento($emprestimo);
        } catch (RegraNegocioException $e) {
            return back()->withErrors(['regra' => $e->getMessage()]);
        }

        return back()->with('status', 'Pagamento da contribuição registrado. Valor destinado às ações sociais da empresa.');
    }
}
