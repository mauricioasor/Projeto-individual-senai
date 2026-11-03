<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * R.F. 05 – registro de livros (RN09 e RN10).
 */
class LivroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo' => trim(preg_replace('/\s+/', ' ', (string) $this->input('titulo'))),
            // ISBN armazenado sem hífens nem espaços
            'isbn' => strtoupper(preg_replace('/[^0-9Xx]/', '', (string) $this->input('isbn'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'isbn' => ['required', 'regex:/^(\d{13}|\d{9}[\dX])$/',
                Rule::unique('LIVRO', 'isbn')->ignore($this->route('livro')?->id_livro, 'id_livro')],
            'ano_publicacao' => ['nullable', 'integer', 'min:1450', 'max:'.(now()->year + 1)],
            'quantidade_total' => ['required', 'integer', 'min:1', 'max:999'],
            'id_categoria' => ['required', 'integer', 'exists:CATEGORIA,id_categoria'],
            'autores' => ['required', 'array', 'min:1'],
            'autores.*' => ['integer', 'distinct', 'exists:AUTOR,id_autor'],
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.regex' => 'Informe um ISBN com 10 ou 13 dígitos.',
            'isbn.unique' => 'Já existe um livro cadastrado com este ISBN.',
            'autores.required' => 'Selecione pelo menos um autor.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_categoria' => 'categoria',
            'quantidade_total' => 'quantidade de exemplares',
            'ano_publicacao' => 'ano de publicação',
        ];
    }
}
