<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $senha = null;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->userName().'@'.config('biblioteca.dominio_email'),
            'senha' => static::$senha ??= Hash::make('Senha@123'),
            'tipo_perfil' => Usuario::USUARIO,
            'situacao' => Usuario::ATIVO,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['tipo_perfil' => Usuario::ADMIN]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['situacao' => Usuario::INATIVO]);
    }
}
