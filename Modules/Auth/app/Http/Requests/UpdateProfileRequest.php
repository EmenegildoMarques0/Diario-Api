<?php
namespace Modules\Auth\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        return [
            'name' => 'sometimes|nullable|string|max:150',
            'username' => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => 'sometimes|nullable|string|max:5000',
            'avatar' => [
                'sometimes',
                'nullable',
                Rule::when($this->hasFile('avatar'), ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048']),
                Rule::when(
                    fn() => in_array($user->role, ['editor', 'admin']) && $this->hasFile('avatar'),
                    ['required']
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'O avatar é obrigatório para editores e administradores.',
            'avatar.image' => 'O avatar deve ser uma imagem (JPEG, PNG, JPG, GIF, WEBP).',
            'avatar.mimes' => 'O avatar deve ser do tipo JPEG, PNG, JPG, GIF ou WEBP.',
            'avatar.max' => 'O avatar não pode exceder 2MB.',
        ];
    }
}
