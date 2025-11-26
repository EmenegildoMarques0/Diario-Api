<?php

namespace Modules\Auth\app\Http\Requests;



use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role === 'admin';
    }

    public function rules()
    {
        return [
            'role' => 'required|in:admin,editor,reader',
        ];
    }

    public function messages()
    {
        return [
            'role.required' => 'O campo role é obrigatório.',
            'role.in' => 'O role deve ser "admin", "editor" ou "reader".',
        ];
    }
}
