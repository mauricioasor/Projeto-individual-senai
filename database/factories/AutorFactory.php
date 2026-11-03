<?php

namespace Database\Factories;

use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Autor>
 */
class AutorFactory extends Factory
{
    protected $model = Autor::class;

    public function definition(): array
    {
        return ['nome_autor' => fake()->unique()->name()];
    }
}
