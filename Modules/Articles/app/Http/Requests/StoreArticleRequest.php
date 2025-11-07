<?php

namespace Modules\Articles\app\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Articles\app\Models\Article;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Article::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'slug' => 'required|string|unique:articles,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'is_published' => 'sometimes|boolean|string',
            'is_featured' => 'sometimes|boolean|string',

            // Imagem de capa
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Galeria
            'gallery_images' => 'sometimes|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
