<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedefinirSenhaRequest;
use App\Mail\RecuperacaoSenhaMail;
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * R.F. 07 – recuperação de senha por e-mail (RN07 e R.N.F. 10).
 * O token vai apenas no e-mail; no banco fica somente o seu hash SHA-256.
 */
class RecuperacaoSenhaController extends Controller
{
    private const MSG_ENVIADO = 'Se o e-mail estiver cadastrado, você receberá em instantes um link para redefinir a senha.';

    public function create(): View
    {
        return view('auth.esqueci-senha');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:160']]);

        $usuario = Usuario::ativos()->where('email', mb_strtolower(trim($request->input('email'))))->first();

        if ($usuario) {
            $token = Str::random(64);

            DB::transaction(function () use ($usuario, $token) {
                // Links anteriores deixam de valer
                RecuperacaoSenha::where('id_usuario', $usuario->id_usuario)->where('utilizado', false)->update(['utilizado' => true]);

                RecuperacaoSenha::create([
                    'id_usuario' => $usuario->id_usuario,
                    'token_hash' => RecuperacaoSenha::hashDoToken($token),
                    'data_expiracao' => now()->addMinutes((int) config('biblioteca.token_minutos')),
                    'utilizado' => false,
                ]);
            });

            Mail::to($usuario->email)->send(new RecuperacaoSenhaMail($usuario, route('senha.redefinir', $token)));
        }

        // Mesma resposta exista ou não a conta (R.N.F. 10)
        return back()->with('status', self::MSG_ENVIADO);
    }

    public function edit(string $token): View
    {
        $valido = strlen($token) === 64 && RecuperacaoSenha::valido($token)->exists();

        return view('auth.redefinir-senha', ['token' => $token, 'valido' => $valido]);
    }

    public function update(RedefinirSenhaRequest $request): RedirectResponse
    {
        $ok = DB::transaction(function () use ($request) {
            $recuperacao = RecuperacaoSenha::valido($request->validated('token'))->lockForUpdate()->first();

            if (! $recuperacao) {
                return false;
            }

            $usuario = $recuperacao->usuario;
            $usuario->senha = $request->validated('senha');
            $usuario->tentativas_login = 0;
            $usuario->bloqueado_ate = null;
            $usuario->save();

            $recuperacao->update(['utilizado' => true]);

            return true;
        });

        if (! $ok) {
            return redirect()->route('senha.solicitar')
                ->withErrors(['email' => 'Link inválido ou expirado. Solicite uma nova recuperação de senha.']);
        }

        return redirect()->route('login')->with('status', 'Senha redefinida com sucesso. Entre com a nova senha.');
    }
}
