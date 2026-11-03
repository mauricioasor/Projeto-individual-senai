<?php

namespace Database\Factories;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Livro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Livro>
 */
class LivroFactory extends Factory
{
    protected $model = Livro::class;

    public function definition(): array
    {
        return [
            'titulo' => rtrim(fake()->sentence(3), '.'),
            'isbn' => fake()->unique()->numerify('978#########0'),
            'ano_publicacao' => fake()->numberBetween(2000, 2026),
            'quantidade_total' => 3,
            'quantidade_disponivel' => 3,
            'id_categoria' => Categoria::factory(),
        ];
    }

    /** Livro com uma quantidade específica de exemplares. */
    public function exemplares(int $total, ?int $disponiveis = null): static
    {
        return $this->state(fn () => ['quantidade_total' => $total, 'quantidade_disponivel' => $disponiveis ?? $total]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Livro $livro) {
            if ($livro->autores()->doesntExist()) {
                $livro->autores()->attach(Autor::factory()->create());
            }
        });
    }
}
