<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoria temática do acervo (tabela CATEGORIA).
 */
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $table = 'CATEGORIA';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;

    protected $fillable = ['nome_categoria'];

    public function livros(): HasMany
    {
        return $this->hasMany(Livro::class, 'id_categoria', 'id_categoria');
    }
}
