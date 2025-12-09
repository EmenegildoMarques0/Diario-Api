<?php

namespace Modules\Articles\app\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'slug'          => $this->slug,
            'title'         => $this->title,
            'excerpt'       => $this->excerpt,
            'content'       => $this->content,

            'author' => $this->whenLoaded('author', fn() => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ]),

            'published_by' => $this->whenLoaded('publisher', fn() => [
                'id'   => $this->publisher->id,
                'name' => $this->publisher->name,
            ]),

            'is_published'  => $this->is_published,
            'is_featured'   => $this->is_featured,

            'published_at'  => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'), // adicionado
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),

            'view_count'    => $this->view_count ?? 0,

            // Imagens
            'cover_image' => $this->whenLoaded('coverImage', fn() => $this->coverImage->url),

            'gallery' => $this->whenLoaded('images', fn() =>
                $this->images
                    ->where('is_cover', false)
                    ->sortBy('sort_order')
                    ->pluck('url')
                    ->toArray()
            ),

            // APENAS O NOME DA CATEGORIA
            'categories' => $this->whenLoaded('categories', fn() =>
                $this->categories->pluck('name')->toArray()
            , []),

            // Exemplo de saída: ["Tecnologia", "Laravel", "Notícias"]
        ];
    }
}
