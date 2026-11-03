<?php

namespace App\Models;

use Database\Factories\LivroFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Título do acervo (tabela LIVRO).
 *
 * @property int $id_livro
 * @property string $titulo
 * @property string $isbn
 * @property int $quantidade_total
 * @property int $quantidade_disponivel
 */
class Livro extends Model
{
    /** @use HasFactory<LivroFactory> */
    use HasFactory;

    protected $table = 'LIVRO';
    protected $primaryKey = 'id_livro';
    public $timestamps = false;

    protected $fillable = [
        'titulo', 'isbn', 'ano_publicacao', 'quantidade_total', 'quantidade_disponivel', 'id_categoria',
    ];

    protected function casts(): array
    {
        return [
            'ano_publicacao' => 'integer',
            'quantidade_total' => 'integer',
            'quantidade_disponivel' => 'integer',
            'id_categoria' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /** RN09 – um livro pode ter vários autores (N:N por LIVRO_AUTOR). */
    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'LIVRO_AUTOR', 'id_livro', 'id_autor')->orderBy('nome_autor');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemEmprestimo::class, 'id_livro', 'id_livro');
    }

    public function nomesAutores(): string
    {
        return $this->autores->pluck('nome_autor')->join(', ');
    }

    /** Situação exibida no catálogo (R.F. 15). */
    public function situacao(): array
    {
        if ($this->quantidade_disponivel <= 0) {
            return ['Indisponível', 'danger', 'bi-x-circle-fill'];
        }

        if ($this->quantidade_disponivel === 1 && $this->quantidade_total > 1) {
            return ['Último exemplar', 'warning', 'bi-exclamation-circle-fill'];
        }

        return ['Disponível', 'success', 'bi-check-circle-fill'];
    }

    public function isbnFormatado(): string
    {
        $i = $this->isbn;

        return strlen($i) === 13
            ? substr($i, 0, 3).'-'.substr($i, 3, 2).'-'.substr($i, 5, 2).'-'.substr($i, 7, 5).'-'.substr($i, 12)
            : $i;
    }
}
