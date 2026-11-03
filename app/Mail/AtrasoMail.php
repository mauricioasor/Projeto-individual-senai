<?php

namespace App\Mail;

use App\Models\Emprestimo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * R.F. 12 – notificação de empréstimo em atraso.
 */
class AtrasoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Emprestimo $emprestimo)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MR Biblioteca – empréstimo em atraso');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.atraso', with: [
            'itens' => $this->emprestimo->itens()->whereNull('data_devolucao')->with('livro')->get(),
            'valorDia' => (float) config('biblioteca.valor_dia_atraso'),
        ]);
    }
}
