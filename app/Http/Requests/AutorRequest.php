<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * R.F. 03 – registro de autores.
 */
class AutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nome_autor' => trim(preg_replace('/\s+/', ' ', (string) $this->input('nome_autor')))]);
    }

    public function rules(): array
    {
        return [
            'nome_autor' => ['required', 'string', 'min:2', 'max:120',
                Rule::unique('AUTOR', 'nome_autor')->ignore($this->route('autor')?->id_autor, 'id_autor')],
        ];
    }
}
