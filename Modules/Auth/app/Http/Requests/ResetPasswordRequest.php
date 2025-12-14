<?php

namespace Modules\Auth\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token' => ['required'], // O token enviado por e-mail
            'email' => [
                'required',
                'email',
                'exists:users,email' // Garante que o e-mail existe
            ],
            'password' => [
                'required',
                'confirmed', // Deve haver um campo 'password_confirmation' correspondente
                Password::min(6) // Mínimo de 8 caracteres
                    ->mixedCase()  // Deve conter letras maiúsculas e minúsculas
                    ->letters()    // Deve conter letras
                    ->numbers()    // Deve conter números
                    ->symbols()    // Deve conter símbolos (opcional, mas recomendado)
                    ->uncompromised(), // Verifica se a senha vazou em bases de dados públicas (opcional)
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            // Validações diretas
            'token.required' => 'O token de redefinição de senha é obrigatório.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O e-mail fornecido não é válido.',
            'email.exists' => 'O e-mail fornecido não está associado a nenhuma conta.',

            'password.required' => 'O campo nova senha é obrigatório.',
            'password.confirmed' => 'A confirmação da nova senha não coincide.',

            // As mensagens detalhadas para as regras Password:: (mixedCase, symbols, etc.)
            // devem ser configuradas via arquivos de tradução do Laravel (lang/pt_BR/validation.php).
            // A mensagem 'password.min' abaixo é para a regra :min simples.
            // Para as regras complexas, o Laravel usa a tradução.
        ];
    }
}
