<?php
namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Articles\app\Http\Requests\StoreArticleRequest;
use Modules\Articles\app\Http\Requests\UpdateArticleRequest;
use Modules\Articles\app\Models\Article;
use Modules\Articles\app\Transformers\ArticleResource;
use Modules\Articles\app\Transformers\ArticleCollection;

class ArticleAnnotationController extends Controller
{
    /**
     * Schemas embutidos — usando JsonContent inline nas respostas para evitar referências que não sejam encontradas pelo scanner.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Artigos"},
     *     summary="Lista todos os artigos",
     *     description="Retorna uma lista paginada de artigos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de artigos retornada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Título do Artigo"),
     *                     @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     *                     @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     *                     @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     *                     @OA\Property(property="is_published", type="boolean", example=true),
     *                     @OA\Property(property="is_featured", type="boolean", example=false),
     *                     @OA\Property(
     *                         property="author",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Autor do Artigo"),
     *                         @OA\Property(property="email", type="string", format="email", example="author@example.com")
     *                     ),
     *                     @OA\Property(
     *                         property="publisher",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Editor do Artigo"),
     *                         @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     *                     ),
     *                     @OA\Property(
     *                         property="coverImage",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     *                         @OA\Property(property="is_cover", type="boolean", example=true)
     *                     ),
     *                     @OA\Property(
     *                         property="images",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="path", type="string", example="articles/1/img1.jpg")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        // Implementação do método index
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/articles",
     *     tags={"Artigos"},
     *     summary="Cria um novo artigo",
     *     description="Cria um artigo com opção de publicação e upload de imagens",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "slug", "content"},
     *                 @OA\Property(property="title", type="string", maxLength=150, example="Título do Artigo"),
     *                 @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     *                 @OA\Property(property="excerpt", type="string", maxLength=500, example="Resumo do artigo"),
     *                 @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="is_featured", type="boolean", example=false),
     *                 @OA\Property(property="cover_image", type="file", description="Imagem de capa do artigo"),
     *                 @OA\Property(
     *                     property="gallery_images[]",
     *                     type="array",
     *                     @OA\Items(type="file", description="Imagens da galeria do artigo")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artigo criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Título do Artigo"),
     *             @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     *             @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     *             @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     *             @OA\Property(property="is_published", type="boolean", example=true),
     *             @OA\Property(property="is_featured", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao criar artigo")
     * )
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        // Implementação do método store
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/{slug}",
     *     tags={"Artigos"},
     *     summary="Exibe um artigo específico",
     *     description="Retorna os dados de um artigo pelo slug",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo retornado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Título do Artigo"),
     *             @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     *             @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     *             @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     *             @OA\Property(property="is_published", type="boolean", example=true),
     *             @OA\Property(property="is_featured", type="boolean", example=false),
     *             @OA\Property(
     *                 property="author",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Autor do Artigo")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Artigo não encontrado")
     * )
     */
    public function show(Article $article): JsonResponse
    {
        // Implementação do método show
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/articles/{id}",
     *     tags={"Artigos"},
     *     summary="Atualiza um artigo existente",
     *     description="Atualiza dados de um artigo, incluindo publicação e imagens",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", maxLength=150),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="excerpt", type="string", maxLength=500),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="is_published", type="boolean"),
     *                 @OA\Property(property="is_featured", type="boolean"),
     *                 @OA\Property(property="cover_image", type="file", description="Imagem de capa do artigo"),
     *                 @OA\Property(
     *                     property="gallery_images[]",
     *                     type="array",
     *                     @OA\Items(type="file", description="Imagens da galeria do artigo")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Título do Artigo"),
     *             @OA\Property(property="slug", type="string", example="titulo-do-artigo")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao atualizar artigo")
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        // Implementação do método update
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/articles/{id}",
     *     tags={"Artigos"},
     *     summary="Exclui um artigo",
     *     description="Exclui um artigo permanentemente",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Artigo excluído com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Ação não autorizada"),
     *     @OA\Response(response=500, description="Erro ao excluir artigo")
     * )
     */
    public function destroy(Article $article): JsonResponse
    {
        // Implementação do método destroy
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/articles/{id}/restore",
     *     tags={"Artigos"},
     *     summary="Restaura um artigo excluído",
     *     description="Restaura um artigo que foi deletado soft-delete",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo restaurado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Artigo restaurado.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Ação não autorizada"),
     *     @OA\Response(response=404, description="Artigo não encontrado"),
     *     @OA\Response(response=500, description="Erro ao restaurar artigo")
     * )
     */
    public function restore($id): JsonResponse
    {
        // Implementação do método restore
    }
}
