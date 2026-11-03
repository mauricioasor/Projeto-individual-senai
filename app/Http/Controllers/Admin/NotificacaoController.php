<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AtrasoMail;
use App\Models\Emprestimo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

/**
 * R.F. 12 – notificação de atraso por e-mail, individual ou em lote.
 */
class NotificacaoController extends Controller
{
    public function um(Emprestimo $emprestimo): RedirectResponse
    {
        if (! $emprestimo->estaAtrasado()) {
            return back()->withErrors(['regra' => 'Este empréstimo não está em atraso.']);
        }

        $emprestimo->loadMissing('usuario');
        Mail::to($emprestimo->usuario->email)->send(new AtrasoMail($emprestimo));

        return back()->with('status', 'Notificação enviada para '.$emprestimo->usuario->email.'.');
    }

    public function todos(): RedirectResponse
    {
        $atrasados = Emprestimo::atrasados()->with('usuario')->get();

        foreach ($atrasados as $emprestimo) {
            Mail::to($emprestimo->usuario->email)->send(new AtrasoMail($emprestimo));
        }

        return back()->with('status', $atrasados->isEmpty()
            ? 'Não há empréstimos em atraso.'
            : "Notificações enviadas para {$atrasados->count()} empréstimo(s) em atraso.");
    }
}
