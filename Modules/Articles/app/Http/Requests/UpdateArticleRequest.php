<?php

namespace Modules\Articles\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'title' => 'sometimes|string|max:150',
            'slug' => 'sometimes|string|unique:articles,slug,' . $article->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'is_published' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',

            'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images' => 'sometimes|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
