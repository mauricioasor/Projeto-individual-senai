<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * R.F. 02 – autocadastro de colaborador (RN02 e RN04).
 */
class CadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => trim(preg_replace('/\s+/', ' ', (string) $this->input('nome'))),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $dominio = preg_quote(config('biblioteca.dominio_email'), '/');

        return [
            'nome' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:160', "regex:/@{$dominio}$/i", 'unique:USUARIO,email'],
            'senha' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'max:72'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Use o seu e-mail corporativo (@'.config('biblioteca.dominio_email').').',
            'email.unique' => 'Já existe uma conta com este e-mail. Use a opção "Esqueceu a senha?" se precisar.',
        ];
    }
}
