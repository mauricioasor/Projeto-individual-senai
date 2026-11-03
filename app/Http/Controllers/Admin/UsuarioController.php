<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\Usuario;
use App\Services\EmprestimoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * R.F. 01 – registro de usuários (RN03 e RN05).
 */
class UsuarioController extends Controller
{
    public function index(Request $request, EmprestimoService $servico): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'perfil' => ['nullable', 'in:ADMIN,USUARIO'],
            'situacao' => ['nullable', 'in:ATIVO,INATIVO'],
        ]);
        $busca = trim($filtros['q'] ?? '');

        $usuarios = Usuario::query()
            ->withCount([
                'emprestimos',
                'emprestimos as ativos_count' => fn ($q) => $q->ativos(),
                'emprestimos as atrasados_count' => fn ($q) => $q->atrasados(),
                'emprestimos as pendentes_count' => fn ($q) => $q->comContribuicaoPendente(),
            ])
            ->when($busca !== '', fn ($q) => $q->where(fn ($q) => $q->where('nome', 'like', "%{$busca}%")->orWhere('email', 'like', "%{$busca}%")))
            ->when($filtros['perfil'] ?? null, fn ($q, $p) => $q->where('tipo_perfil', $p))
            ->when($filtros['situacao'] ?? null, fn ($q, $s) => $q->where('situacao', $s))
            ->orderBy('nome')
            ->paginate(12)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'total' => Usuario::count(),
            'ativos' => Usuario::ativos()->count(),
            'admins' => Usuario::where('tipo_perfil', Usuario::ADMIN)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.form', ['usuario' => new Usuario()]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        Usuario::create($request->validated());

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário cadastrado com sucesso.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.form', ['usuario' => $usuario]);
    }

    public function update(UsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $dados = $request->validated();
        if (empty($dados['senha'])) {
            unset($dados['senha']);
        }

        // O Administrador não pode retirar o próprio acesso
        if ($usuario->is($request->user()) && ($dados['tipo_perfil'] !== Usuario::ADMIN || $dados['situacao'] !== Usuario::ATIVO)) {
            return back()->withErrors(['regra' => 'Você não pode alterar o próprio perfil nem inativar a própria conta.'])->withInput();
        }

        $usuario->update($dados);

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function alternarSituacao(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['regra' => 'Você não pode inativar a própria conta.']);
        }

        $usuario->update(['situacao' => $usuario->isAtivo() ? Usuario::INATIVO : Usuario::ATIVO]);

        return back()->with('status', 'Usuário '.($usuario->isAtivo() ? 'reativado' : 'inativado').' com sucesso.');
    }

    /** RN05 – quem tem empréstimos registrados não é excluído, apenas inativado. */
    public function destroy(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['regra' => 'Você não pode excluir a própria conta.']);
        }
        if ($usuario->emprestimos()->exists()) {
            return back()->withErrors(['regra' => 'Este usuário possui empréstimos registrados e não pode ser excluído. Inative a conta para preservar o histórico.']);
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário excluído.');
    }
}
