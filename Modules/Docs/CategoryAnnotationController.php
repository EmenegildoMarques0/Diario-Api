<?php
namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Articles\app\Http\Requests\CategoryRequest;
use Modules\Articles\app\Models\Category;
use Modules\Articles\app\Transformers\CategoryResource;

class CategoryAnnotationController extends Controller
{
    /**
     * Schemas embutidos — usando JsonContent inline nas respostas para evitar referências que não sejam encontradas pelo scanner.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories",
     *     tags={"Categorias Admin"},
     *     summary="Lista todas as categorias",
     *     description="Retorna uma lista de categorias, incluindo as deletadas (soft delete), com informações do criador. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorias retornada com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Notícias"),
     *                 @OA\Property(property="slug", type="string", example="noticias"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Categoria para notícias gerais"),
     *                 @OA\Property(
     *                     property="created_by",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Editor"),
     *                     @OA\Property(property="email", type="string", format="email", example="editor@example.com")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor.")
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
     *     path="/api/v1/admin/categories",
     *     tags={"Categorias Admin"},
     *     summary="Cria uma nova categoria",
     *     description="Cria uma categoria com nome e descrição opcional. O slug é gerado automaticamente. Requer autenticação e papel de editor ou admin.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=100, example="Notícias"),
     *             @OA\Property(property="description", type="string", maxLength=500, nullable=true, example="Categoria para notícias gerais")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoria criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Notícias"),
     *             @OA\Property(property="slug", type="string", example="noticias"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Categoria para notícias gerais"),
     *             @OA\Property(
     *                 property="created_by",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Editor"),
     *                 @OA\Property(property="email", type="string", format="email", example="editor@example.com")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ação não autorizada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao criar categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao criar categoria: Falha no servidor.")
     *         )
     *     )
     * )
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        // Implementação do método store
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories/{slug}",
     *     tags={"Categorias Admin"},
     *     summary="Exibe uma categoria específica",
     *     description="Retorna os detalhes de uma categoria pelo slug, incluindo informações do criador. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug da categoria",
     *         @OA\Schema(type="string", example="noticias")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria retornada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Notícias"),
     *             @OA\Property(property="slug", type="string", example="noticias"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Categoria para notícias gerais"),
     *             @OA\Property(
     *                 property="created_by",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Editor"),
     *                 @OA\Property(property="email", type="string", format="email", example="editor@example.com")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao exibir categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao exibir categoria.")
     *         )
     *     )
     * )
     */
    public function show(Category $category): JsonResponse
    {
        // Implementação do método show
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/categories/{slug}",
     *     tags={"Categorias Admin"},
     *     summary="Atualiza uma categoria existente",
     *     description="Atualiza os dados de uma categoria pelo slug. Requer autenticação e papel de editor ou admin.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug da categoria",
     *         @OA\Schema(type="string", example="noticias")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=100, example="Notícias Atualizada"),
     *             @OA\Property(property="description", type="string", maxLength=500, nullable=true, example="Categoria atualizada para notícias gerais")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Notícias Atualizada"),
     *             @OA\Property(property="slug", type="string", example="noticias-atualizada"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Categoria atualizada para notícias gerais"),
     *             @OA\Property(
     *                 property="created_by",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Editor"),
     *                 @OA\Property(property="email", type="string", format="email", example="editor@example.com")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ação não autorizada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The name field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar categoria.")
     *         )
     *     )
     * )
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        // Implementação do método update
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/categories/{slug}",
     *     tags={"Categorias Admin"},
     *     summary="Exclui uma categoria",
     *     description="Realiza exclusão lógica (soft delete) de uma categoria pelo slug. Requer autenticação e papel de editor ou admin.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug da categoria",
     *         @OA\Schema(type="string", example="noticias")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoria deletada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ação não autorizada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao excluir categoria: Falha no servidor.")
     *         )
     *     )
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        // Implementação do método destroy
    }
}
