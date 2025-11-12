<?php

namespace Modules\Articles\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'editor' || auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ];
    }

    protected function prepareForValidation()
    {
        $baseSlug = Str::slug($this->name);
        $slug = $baseSlug;

        // Garante que o slug seja único
        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . rand(0, 5000);
        }

        $this->merge([
            'slug' => $slug,
        ]);
    }
}
