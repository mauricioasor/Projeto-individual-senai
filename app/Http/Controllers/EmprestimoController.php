<?php

namespace App\Http\Controllers;

use App\Exceptions\RegraNegocioException;
use App\Http\Requests\EmprestimoRequest;
use App\Models\Emprestimo;
use App\Models\Livro;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * R.F. 08 (registro, mestre-detalhe) e R.F. 09 (renovação).
 */
class EmprestimoController extends Controller
{
    public function __construct(private EmprestimoService $servico)
    {
    }

    public function create(Request $request): View
    {
        $usuario = $request->user();

        return view('emprestimos.create', [
            'livros' => Livro::with('autores')->where('quantidade_disponivel', '>', 0)->orderBy('titulo')->get(),
            'colaboradores' => $usuario->isAdmin() ? Usuario::ativos()->orderBy('nome')->get() : collect(),
            'livroInicial' => $request->integer('livro') ?: null,
            'colaboradorInicial' => $request->integer('usuario') ?: null,
            'maxLivros' => (int) config('biblioteca.max_livros'),
            'prazoDias' => (int) config('biblioteca.prazo_dias'),
            'pendencias' => $usuario->isAdmin() ? [] : $this->servico->pendencias($usuario),
            'emPosse' => $usuario->isAdmin() ? 0 : $this->servico->livrosEmPosse($usuario),
        ]);
    }

    public function store(EmprestimoRequest $request): RedirectResponse
    {
        $colaborador = Usuario::findOrFail($request->validated('id_usuario'));

        try {
            $emprestimo = $this->servico->registrar($colaborador, $request->validated('livros'));
        } catch (RegraNegocioException $e) {
            return back()->withErrors(['regra' => $e->getMessage()])->withInput();
        }

        $mensagem = 'Empréstimo '.$emprestimo->codigo().' registrado. Devolução prevista para '
            .$emprestimo->data_prevista_dev->format('d/m/Y').'.';

        return $request->user()->isAdmin()
            ? redirect()->route('admin.emprestimos.show', $emprestimo)->with('status', $mensagem)
            : redirect()->route('painel.colaborador')->with('status', $mensagem);
    }

    public function renovar(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        Gate::authorize('renovar', $emprestimo);

        try {
            $this->servico->renovar($emprestimo);
        } catch (RegraNegocioException $e) {
            return back()->withErrors(['regra' => $e->getMessage()]);
        }

        return back()->with('status', 'Empréstimo renovado. Nova data de devolução: '.$emprestimo->data_prevista_dev->format('d/m/Y').'.');
    }
}
