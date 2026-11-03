<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * R.F. 07 – nova senha a partir do link de recuperação.
 */
class RedefinirSenhaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:64'],
            'senha' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'max:72'],
        ];
    }
}
