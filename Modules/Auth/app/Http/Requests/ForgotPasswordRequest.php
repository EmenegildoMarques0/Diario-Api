<?php

namespace Modules\Auth\app\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Altere para 'true' se esta rota for acessível publicamente (como é comum para recuperação de senha)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email' // Verifica se o e-mail existe na tabela 'users'
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O e-mail fornecido não é válido.',
            // É comum dar uma mensagem genérica aqui por segurança, mas 'exists' ajuda a guiar o usuário:
            'email.exists' => 'Não encontramos um usuário com este e-mail.',
        ];
    }
}
