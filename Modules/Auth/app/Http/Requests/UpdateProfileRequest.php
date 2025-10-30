<?php

namespace Modules\Auth\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorização via Policy ou manual no controller
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => 'sometimes|string|max:150',
            'username' => 'sometimes|string|max:80|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:5000',
            'avatar' => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
                // Regra condicional: apenas editor/admin podem enviar avatar
                Rule::requiredIf(fn() => in_array($user->role, ['editor', 'admin'])),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'O avatar é obrigatório para editores e administradores.',
        ];
    }


}
