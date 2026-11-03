<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Livro de um empréstimo – detalhe do formulário mestre-detalhe (tabela ITEM_EMPRESTIMO).
 */
class ItemEmprestimo extends Model
{
    protected $table = 'ITEM_EMPRESTIMO';
    protected $primaryKey = 'id_item';
    public $timestamps = false;

    protected $fillable = ['id_emprestimo', 'id_livro', 'data_devolucao', 'dias_atraso', 'valor_contribuicao_item'];

    protected $attributes = [
        'dias_atraso' => 0,
        'valor_contribuicao_item' => 0,
    ];

    protected function casts(): array
    {
        return [
            'id_emprestimo' => 'integer',
            'id_livro' => 'integer',
            'data_devolucao' => 'datetime',
            'dias_atraso' => 'integer',
            'valor_contribuicao_item' => 'decimal:2',
        ];
    }

    public function emprestimo(): BelongsTo
    {
        return $this->belongsTo(Emprestimo::class, 'id_emprestimo', 'id_emprestimo');
    }

    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class, 'id_livro', 'id_livro');
    }

    public function devolvido(): bool
    {
        return $this->data_devolucao !== null;
    }
}
