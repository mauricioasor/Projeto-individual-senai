<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Colaborador cadastrado no sistema (tabela USUARIO).
 *
 * @property int $id_usuario
 * @property string $nome
 * @property string $email
 * @property string $tipo_perfil
 * @property string $situacao
 */
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    public const ADMIN = 'ADMIN';
    public const USUARIO = 'USUARIO';
    public const ATIVO = 'ATIVO';
    public const INATIVO = 'INATIVO';

    protected $table = 'USUARIO';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    /** A senha fica na coluna "senha" (R.N.F. 03). */
    protected $authPasswordName = 'senha';

    /** A tabela não tem coluna de "lembrar-me". */
    protected $rememberTokenName = '';

    protected $fillable = ['nome', 'email', 'senha', 'tipo_perfil', 'situacao'];

    protected $hidden = ['senha'];

    protected $attributes = [
        'tipo_perfil' => self::USUARIO,
        'situacao' => self::ATIVO,
        'tentativas_login' => 0,
    ];

    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
            'tentativas_login' => 'integer',
            'bloqueado_ate' => 'datetime',
            'data_cadastro' => 'datetime',
        ];
    }

    /** E-mail sempre em minúsculas, sem espaços (RN02). */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim((string) $value));
    }

    public function emprestimos(): HasMany
    {
        return $this->hasMany(Emprestimo::class, 'id_usuario', 'id_usuario');
    }

    public function isAdmin(): bool
    {
        return $this->tipo_perfil === self::ADMIN;
    }

    public function isAtivo(): bool
    {
        return $this->situacao === self::ATIVO;
    }

    /** RN06 – conta bloqueada temporariamente por tentativas inválidas. */
    public function estaBloqueado(): bool
    {
        return $this->bloqueado_ate !== null && $this->bloqueado_ate->isFuture();
    }

    public function iniciais(): string
    {
        $partes = preg_split('/\s+/', trim($this->nome)) ?: [];
        $primeira = mb_substr($partes[0] ?? '', 0, 1);
        $ultima = count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '';

        return mb_strtoupper($primeira.$ultima);
    }

    public function primeiroNome(): string
    {
        return explode(' ', trim($this->nome))[0];
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('situacao', self::ATIVO);
    }
}
