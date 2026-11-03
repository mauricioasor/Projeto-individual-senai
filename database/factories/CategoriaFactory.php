<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return ['nome_categoria' => ucfirst(fake()->unique()->word()).' '.fake()->unique()->numberBetween(1, 9999)];
    }
}
