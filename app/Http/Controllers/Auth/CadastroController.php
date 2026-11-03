<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CadastroRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * R.F. 02 – autocadastro do colaborador, sempre com perfil Usuário (RN04).
 */
class CadastroController extends Controller
{
    public function create(): View
    {
        return view('auth.cadastro', ['dominio' => config('biblioteca.dominio_email')]);
    }

    public function store(CadastroRequest $request): RedirectResponse
    {
        Usuario::create([
            'nome' => $request->validated('nome'),
            'email' => $request->validated('email'),
            'senha' => $request->validated('senha'),
            'tipo_perfil' => Usuario::USUARIO,
            'situacao' => Usuario::ATIVO,
        ]);

        return redirect()->route('login')
            ->with('status', 'Conta criada com sucesso. Entre com seu e-mail e senha.');
    }
}
