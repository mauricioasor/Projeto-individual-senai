<?php

namespace App\Http\Controllers;

use App\Models\Emprestimo;
use App\Models\ItemEmprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PainelController extends Controller
{
    /** Direciona para a página inicial do perfil (R.F. 06). */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->isAdmin() ? 'admin.painel' : 'painel.colaborador');
    }

    /** R.F. 13 – painel do Administrador. */
    public function admin(): View
    {
        $exemplares = (int) Livro::sum('quantidade_total');
        $disponiveis = (int) Livro::sum('quantidade_disponivel');

        $idsComPendencia = Emprestimo::query()
            ->where(fn ($q) => $q->atrasados())
            ->orWhere(fn ($q) => $q->comContribuicaoPendente())
            ->distinct()
            ->pluck('id_usuario');

        $maisEmprestados = ItemEmprestimo::query()
            ->select('id_livro', DB::raw('COUNT(*) as total'))
            ->whereHas('emprestimo', fn ($q) => $q->where('data_emprestimo', '>=', now()->startOfMonth()))
            ->groupBy('id_livro')
            ->orderByDesc('total')
            ->limit(5)
            ->with('livro.autores')
            ->get();

        return view('admin.painel', [
            'totalTitulos' => Livro::count(),
            'totalExemplares' => $exemplares,
            'disponibilidade' => $exemplares > 0 ? (int) round($disponiveis / $exemplares * 100) : 0,
            'usuariosAtivos' => Usuario::ativos()->count(),
            'usuariosComPendencia' => $idsComPendencia->count(),
            'emprestimosAtivos' => Emprestimo::ativos()->count(),
            'emprestimosAtrasados' => Emprestimo::atrasados()->count(),
            'recentes' => Emprestimo::with(['usuario', 'itens.livro'])->latest('data_emprestimo')->limit(5)->get(),
            'maisEmprestados' => $maisEmprestados,
            'atrasados' => Emprestimo::atrasados()->with(['usuario', 'itens.livro'])->orderBy('data_prevista_dev')->limit(5)->get(),
        ]);
    }

    /** R.F. 14 – painel do colaborador. */
    public function colaborador(Request $request, EmprestimoService $servico): View
    {
        $usuario = $request->user();

        $emAberto = $usuario->emprestimos()->ativos()
            ->with(['itens' => fn ($q) => $q->whereNull('data_devolucao')->with('livro.autores')])
            ->orderBy('data_prevista_dev')
            ->get();

        $contribuicaoPendente = (float) $usuario->emprestimos()->comContribuicaoPendente()->sum('valor_contribuicao');

        return view('colaborador.painel', [
            'usuario' => $usuario,
            'emAberto' => $emAberto,
            'emPosse' => $servico->livrosEmPosse($usuario),
            'maxLivros' => (int) config('biblioteca.max_livros'),
            'proxima' => $emAberto->first()?->data_prevista_dev,
            'contribuicaoPendente' => $contribuicaoPendente,
            'pendencias' => $servico->pendencias($usuario),
        ]);
    }
}
