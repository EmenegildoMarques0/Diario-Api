<?php

namespace Modules\Articles\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Garante que o usuário tem permissão 'update' no artigo
        return $this->user()->can('update', $this->route('article'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Regra Crucial: O uso de 'sometimes' em quase todos os campos
     * garante que o campo só será validado SE estiver presente, o que é
     * essencial para updates parciais (PATCH/PUT).
     */
    public function rules(): array
    {
        // Obtém o artigo da rota para excluir o slug atual na verificação de unicidade
        $article = $this->route('article');

        return [
            'title' => 'sometimes|string|max:150',

            // Garante que o slug é único, exceto para o artigo que está sendo editado
            'slug' => 'sometimes|string|unique:articles,slug,' . $article->id,

            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',

            // Booleans: Aceita strings '0'/'1' ou 'true'/'false' enviadas por formulários
            'is_published' => 'sometimes|in:0,1,true,false',
            'is_featured' => 'sometimes|in:0,1,true,false',

            // Ficheiros: Usamos 'sometimes' e 'image'
            'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Galeria (Array):
            'gallery_images' => 'sometimes|array',

            // CORREÇÃO CRÍTICA: O 'nullable' aqui é vital.
            // Permite que a validação do array passe mesmo se os ficheiros
            // não forem enviados ou se houver problemas de parsing.
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
