<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * R.F. 06 – autenticação de usuário (RN01, RN06 e R.N.F. 04 / 10).
 */
class LoginController extends Controller
{
    private const MSG_INVALIDO = 'E-mail ou senha inválidos.';

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'string', 'max:160'],
            'senha' => ['required', 'string', 'max:72'],
        ]);

        $email = mb_strtolower(trim($dados['email']));
        $maxTentativas = (int) config('biblioteca.max_tentativas_login');
        $bloqueio = (int) config('biblioteca.bloqueio_minutos');
        $msgBloqueio = "Muitas tentativas de acesso. Aguarde {$bloqueio} minutos e tente novamente.";

        $usuario = Usuario::where('email', $email)->first();

        // E-mail inexistente: mesmo comportamento de uma conta real (R.N.F. 10)
        if (! $usuario) {
            $chave = 'login:'.sha1($email.'|'.$request->ip());
            if (RateLimiter::tooManyAttempts($chave, $maxTentativas)) {
                return $this->falha($request, $msgBloqueio);
            }
            RateLimiter::hit($chave, $bloqueio * 60);

            return $this->falha($request, RateLimiter::tooManyAttempts($chave, $maxTentativas) ? $msgBloqueio : self::MSG_INVALIDO);
        }

        // RN06 – conta bloqueada temporariamente
        if ($usuario->estaBloqueado()) {
            return $this->falha($request, $msgBloqueio);
        }

        if (! Hash::check($dados['senha'], $usuario->senha)) {
            $usuario->tentativas_login++;
            if ($usuario->tentativas_login >= $maxTentativas) {
                $usuario->tentativas_login = 0;
                $usuario->bloqueado_ate = now()->addMinutes($bloqueio);
                $usuario->save();

                return $this->falha($request, $msgBloqueio);
            }
            $usuario->save();

            return $this->falha($request, self::MSG_INVALIDO);
        }

        // RN01 – só contas ativas entram (informado apenas após a senha conferir)
        if (! $usuario->isAtivo()) {
            return $this->falha($request, 'Sua conta está inativa. Procure o administrador da biblioteca.');
        }

        $usuario->forceFill(['tentativas_login' => 0, 'bloqueado_ate' => null])->save();

        Auth::login($usuario);
        $request->session()->regenerate(); // evita fixação de sessão

        return redirect()->intended(route('painel'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Você saiu do sistema.');
    }

    private function falha(Request $request, string $mensagem): RedirectResponse
    {
        return back()->withErrors(['email' => $mensagem])->onlyInput('email');
    }
}
