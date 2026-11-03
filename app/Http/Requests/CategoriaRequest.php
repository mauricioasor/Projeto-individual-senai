<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * R.F. 04 – registro de categorias.
 */
class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nome_categoria' => trim(preg_replace('/\s+/', ' ', (string) $this->input('nome_categoria')))]);
    }

    public function rules(): array
    {
        return [
            'nome_categoria' => ['required', 'string', 'min:2', 'max:60',
                Rule::unique('CATEGORIA', 'nome_categoria')->ignore($this->route('categoria')?->id_categoria, 'id_categoria')],
        ];
    }
}
