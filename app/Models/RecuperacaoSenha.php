<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitação de redefinição de senha (tabela RECUPERACAO_SENHA).
 * Apenas o hash SHA-256 do token é armazenado (R.F. 07 / RN07).
 */
class RecuperacaoSenha extends Model
{
    protected $table = 'RECUPERACAO_SENHA';
    protected $primaryKey = 'id_recuperacao';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'token_hash', 'data_expiracao', 'utilizado'];

    protected function casts(): array
    {
        return [
            'id_usuario' => 'integer',
            'data_expiracao' => 'datetime',
            'data_criacao' => 'datetime',
            'utilizado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public static function hashDoToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** Token válido: existe, não foi usado e não expirou. */
    public function scopeValido(Builder $query, string $token): Builder
    {
        return $query->where('token_hash', self::hashDoToken($token))
            ->where('utilizado', false)
            ->where('data_expiracao', '>', now());
    }
}
