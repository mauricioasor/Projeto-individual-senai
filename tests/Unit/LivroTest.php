<?php

namespace Tests\Unit;

use App\Models\Livro;
use PHPUnit\Framework\TestCase;

/**
 * Regras de exibição do livro no catálogo (R.F. 15).
 */
class LivroTest extends TestCase
{
    public function test_situacao_conforme_exemplares_disponiveis(): void
    {
        $livro = new Livro(['quantidade_total' => 3, 'quantidade_disponivel' => 0]);
        $this->assertSame('Indisponível', $livro->situacao()[0]);

        $livro->quantidade_disponivel = 1;
        $this->assertSame('Último exemplar', $livro->situacao()[0]);

        $livro->quantidade_disponivel = 2;
        $this->assertSame('Disponível', $livro->situacao()[0]);
    }

    public function test_isbn_13_formatado(): void
    {
        $livro = new Livro(['isbn' => '9788502000549']);

        $this->assertSame('978-85-02-00054-9', $livro->isbnFormatado());
    }
}
