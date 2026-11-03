<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Empréstimo – mestre do formulário mestre-detalhe (tabela EMPRESTIMO).
 *
 * @property int $id_emprestimo
 * @property int $id_usuario
 * @property Carbon $data_emprestimo
 * @property Carbon $data_prevista_dev
 * @property Carbon|null $data_devolucao
 * @property int $qtd_renovacoes
 * @property string $status
 * @property string $valor_contribuicao
 * @property Carbon|null $data_pagamento_contribuicao
 */
class Emprestimo extends Model
{
    public const ATIVO = 'ATIVO';
    public const DEVOLVIDO = 'DEVOLVIDO';

    protected $table = 'EMPRESTIMO';
    protected $primaryKey = 'id_emprestimo';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario', 'data_emprestimo', 'data_prevista_dev', 'data_devolucao',
        'qtd_renovacoes', 'status', 'valor_contribuicao', 'data_pagamento_contribuicao',
    ];

    protected $attributes = [
        'status' => self::ATIVO,
        'qtd_renovacoes' => 0,
        'valor_contribuicao' => 0,
    ];

    protected function casts(): array
    {
        return [
            'id_usuario' => 'integer',
            'data_emprestimo' => 'datetime',
            'data_prevista_dev' => 'date',
            'data_devolucao' => 'datetime',
            'data_pagamento_contribuicao' => 'datetime',
            'qtd_renovacoes' => 'integer',
            'valor_contribuicao' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemEmprestimo::class, 'id_emprestimo', 'id_emprestimo');
    }

    /** RN20 – atrasado quando a data atual passa da prevista e ainda há livros em posse. */
    public function estaAtrasado(): bool
    {
        return $this->status === self::ATIVO && $this->data_prevista_dev->lt(today());
    }

    public function diasAtraso(): int
    {
        return $this->estaAtrasado() ? (int) $this->data_prevista_dev->diffInDays(today()) : 0;
    }

    public function diasRestantes(): int
    {
        return max(0, (int) today()->diffInDays($this->data_prevista_dev, false));
    }

    public function temContribuicaoPendente(): bool
    {
        return (float) $this->valor_contribuicao > 0 && $this->data_pagamento_contribuicao === null;
    }

    /** Situação exibida nas listagens: [rótulo, cor do badge]. */
    public function situacao(): array
    {
        if ($this->estaAtrasado()) {
            return ['Atrasado', 'danger'];
        }
        if ($this->status === self::ATIVO) {
            return ['Ativo', 'success'];
        }
        if ($this->temContribuicaoPendente()) {
            return ['Contribuição pendente', 'warning'];
        }

        return ['Devolvido', 'info'];
    }

    public function codigo(): string
    {
        return '#'.str_pad((string) $this->id_emprestimo, 4, '0', STR_PAD_LEFT);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('status', self::ATIVO);
    }

    public function scopeAtrasados(Builder $query): Builder
    {
        return $query->where('status', self::ATIVO)->whereDate('data_prevista_dev', '<', today());
    }

    public function scopeEmDia(Builder $query): Builder
    {
        return $query->where('status', self::ATIVO)->whereDate('data_prevista_dev', '>=', today());
    }

    public function scopeComContribuicaoPendente(Builder $query): Builder
    {
        return $query->where('valor_contribuicao', '>', 0)->whereNull('data_pagamento_contribuicao');
    }
}
