<?php

namespace Modules\Articles\app\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class AttachCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'O ID da categoria é obrigatório.',
            'category_id.exists' => 'O ID da categoria fornecido não existe.',
        ];
    }
}
