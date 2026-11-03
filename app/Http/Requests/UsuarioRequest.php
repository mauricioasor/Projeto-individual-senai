<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * R.F. 01 – registro de usuários pelo Administrador.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
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
        /** @var Usuario|null $usuario */
        $usuario = $this->route('usuario');
        $criando = $usuario === null;

        return [
            'nome' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('USUARIO', 'email')->ignore($usuario?->id_usuario, 'id_usuario')],
            'tipo_perfil' => ['required', Rule::in([Usuario::ADMIN, Usuario::USUARIO])],
            'situacao' => ['required', Rule::in([Usuario::ATIVO, Usuario::INATIVO])],
            'senha' => [$criando ? 'required' : 'nullable', 'confirmed', Password::min(8)->letters()->numbers(), 'max:72'],
        ];
    }
}
