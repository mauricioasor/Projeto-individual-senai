<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * R.F. 07 – e-mail com o link de redefinição de senha.
 */
class RecuperacaoSenhaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Usuario $usuario, public string $link)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MR Biblioteca – redefinição de senha');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recuperacao-senha', with: [
            'minutos' => (int) config('biblioteca.token_minutos'),
        ]);
    }
}
