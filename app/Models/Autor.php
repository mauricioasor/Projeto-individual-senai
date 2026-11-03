<?php

namespace App\Models;

use Database\Factories\AutorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Autor de livros (tabela AUTOR).
 */
class Autor extends Model
{
    /** @use HasFactory<AutorFactory> */
    use HasFactory;

    protected $table = 'AUTOR';
    protected $primaryKey = 'id_autor';
    public $timestamps = false;

    protected $fillable = ['nome_autor'];

    public function livros(): BelongsToMany
    {
        return $this->belongsToMany(Livro::class, 'LIVRO_AUTOR', 'id_autor', 'id_livro');
    }
}
