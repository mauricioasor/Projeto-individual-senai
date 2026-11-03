<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * R.F. 08 – formulário mestre-detalhe do empréstimo.
 * Mestre: colaborador. Detalhe: de 1 a 3 livros diferentes (RN13).
 */
class EmprestimoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Linhas do detalhe deixadas em branco são ignoradas
        $livros = array_values(array_filter((array) $this->input('livros', []), fn ($v) => $v !== null && $v !== ''));

        // O colaborador só registra empréstimos para si; o Administrador escolhe o colaborador
        $idUsuario = $this->user()?->isAdmin() ? $this->input('id_usuario') : $this->user()?->id_usuario;

        $this->merge(['livros' => $livros, 'id_usuario' => $idUsuario]);
    }

    public function rules(): array
    {
        $max = (int) config('biblioteca.max_livros');

        return [
            'id_usuario' => ['required', 'integer', 'exists:USUARIO,id_usuario'],
            'livros' => ['required', 'array', 'min:1', "max:{$max}"],
            'livros.*' => ['integer', 'distinct', 'exists:LIVRO,id_livro'],
        ];
    }

    public function messages(): array
    {
        return [
            'livros.required' => 'Adicione pelo menos um livro ao empréstimo.',
            'livros.max' => 'Um empréstimo pode ter no máximo :max livros.',
            'livros.*.distinct' => 'O mesmo livro não pode aparecer duas vezes no empréstimo.',
        ];
    }

    public function attributes(): array
    {
        return ['id_usuario' => 'colaborador'];
    }
}
