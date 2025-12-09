<?php
namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Articles\app\Http\Requests\StoreArticleRequest;
use Modules\Articles\app\Http\Requests\UpdateArticleRequest;
use Modules\Articles\app\Http\Requests\AttachCategoryRequest;
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
     * path="/api/v1/articles",
     * tags={"Artigos"},
     * summary="Lista artigos publicados",
     * description="Retorna uma lista paginada de artigos publicados, com opção de filtro por categorias. Não requer autenticação.",
     * @OA\Parameter(
     * name="category_ids",
     * in="query",
     * required=false,
     * description="IDs das categorias para filtrar (array ou string separada por vírgulas)",
     * @OA\Schema(type="string", example="1,2")
     * ),
     * @OA\Response(
     * response=200,
     * description="Lista de artigos retornada com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=10),
     * @OA\Property(property="per_page", type="integer", example=12),
     * @OA\Property(property="total", type="integer", example=120)
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao carregar artigos",
     * @OA\JsonContent(
     * @OA\Property(property="error", type="string", example="Erro ao carregar os artigos."),
     * @OA\Property(property="message", type="string", example="Ocorreu um problema ao obter os artigos. Verifique os logs para mais detalhes.")
     * )
     * )
     * )
     */
    public function index(): JsonResponse
    {
        // Implementação do método index (ArticlesController)
    }

    /**
     * @OA\Get(
     * path="/api/v1/articles/featured",
     * tags={"Artigos"},
     * summary="Lista artigos em destaque (Featured)",
     * description="Retorna uma lista de artigos publicados e marcados como destaque (`is_featured=true`). Usa cache inteligente. Não requer autenticação.",
     * @OA\Parameter(
     * name="limit",
     * in="query",
     * required=false,
     * description="Limite de artigos a serem retornados (Padrão: 6)",
     * @OA\Schema(type="integer", example=6)
     * ),
     * @OA\Response(
     * response=200,
     * description="Lista de artigos em destaque retornada com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo em Destaque"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo-destaque"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=true),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg")
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao carregar artigos em destaque",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Erro ao carregar artigos em destaque.")
     * )
     * )
     * )
     */
    public function featured(): JsonResponse
    {
        // Implementação do método featured (ArticlesController)
    }

    /**
     * @OA\Get(
     * path="/api/v1/articles/{slug}",
     * tags={"Artigos"},
     * summary="Exibe um artigo publicado",
     * description="Retorna os detalhes de um artigo publicado pelo slug, incluindo autor, editor e imagens. Incrementa a contagem de visualizações. Não requer autenticação.",
     * @OA\Parameter(
     * name="slug",
     * in="path",
     * required=true,
     * description="Slug do artigo",
     * @OA\Schema(type="string", example="titulo-do-artigo")
     * ),
     * @OA\Response(
     * response=200,
     * description="Artigo retornado com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="publisher",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="Editor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Artigo não encontrado ou não publicado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Not Found")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao exibir artigo",
     * @OA\JsonContent(
     * @OA\Property(property="error", type="string", example="Erro ao exibir o artigo."),
     * @OA\Property(property="message", type="string", example="Ocorreu um problema ao exibir o artigo. Verifique os logs para mais detalhes.")
     * )
     * )
     * )
     */
    public function show(Article $article): JsonResponse
    {
        // Implementação do método show (ArticlesController)
    }

    /**
     * @OA\Get(
     * path="/api/v1/admin/articles",
     * tags={"Artigos Admin"},
     * summary="Lista todos os artigos (admin)",
     * description="Retorna uma lista paginada de artigos, incluindo os deletados (soft delete), com autor, editor e imagens. Requer autenticação.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Lista de artigos retornada com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="publisher",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="Editor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     * )
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=10),
     * @OA\Property(property="per_page", type="integer", example=20),
     * @OA\Property(property="total", type="integer", example=150)
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Não autenticado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro interno do servidor",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Erro interno do servidor.")
     * )
     * )
     * )
     */
    public function indexAdmin(): JsonResponse
    {
        // Implementação do método index (AdminArticleController)
    }

    /**
     * @OA\Post(
     * path="/api/v1/admin/articles",
     * tags={"Artigos Admin"},
     * summary="Cria um novo artigo (admin)",
     * description="Cria um artigo com opção de publicação imediata e upload de imagens de capa e galeria. Dispara evento ArticlePublished se publicado. Requer autenticação.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"title", "slug", "content"},
     * @OA\Property(property="title", type="string", maxLength=150, example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", maxLength=255, example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", maxLength=500, example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true, description="Define se o artigo será publicado imediatamente"),
     * @OA\Property(property="is_featured", type="boolean", example=false, description="Define se o artigo é destacado"),
     * @OA\Property(property="cover_image", type="file", description="Imagem de capa do artigo (JPEG, PNG, max: 5MB)"),
     * @OA\Property(
     * property="gallery_images[]",
     * type="array",
     * description="Imagens da galeria do artigo (JPEG, PNG, max: 5MB cada)",
     * @OA\Items(type="file")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Artigo criado com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="publisher",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="Editor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Não autenticado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The title field is required."),
     * @OA\Property(
     * property="errors",
     * type="object",
     * @OA\Property(
     * property="title",
     * type="array",
     * @OA\Items(type="string", example="The title field is required.")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao criar artigo",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Erro ao criar artigo: Falha ao salvar imagem.")
     * )
     * )
     * )
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        // Implementação do método store (AdminArticleController)
    }

    /**
     * @OA\Get(
     * path="/api/v1/admin/articles/{slug}",
     * tags={"Artigos Admin"},
     * summary="Exibe um artigo específico (admin)",
     * description="Retorna os detalhes de um artigo pelo slug, incluindo autor, editor e imagens. Requer autenticação.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="slug",
     * in="path",
     * required=true,
     * description="Slug do artigo",
     * @OA\Schema(type="string", example="titulo-do-artigo")
     * ),
     * @OA\Response(
     * response=200,
     * description="Artigo retornado com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo"),
     * @OA\Property(property="excerpt", type="string", example="Resumo do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo completo do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="publisher",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="Editor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Não autenticado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Artigo não encontrado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Not Found")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao exibir artigo",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Erro ao exibir o artigo.")
     * )
     * )
     * )
     */
    public function showAdmin(Article $article): JsonResponse
    {
        // Implementação do método show (AdminArticleController)
    }

    /**
     * @OA\Put(
     * path="/api/v1/admin/articles/{slug}",
     * tags={"Artigos Admin"},
     * summary="Atualiza um artigo existente (admin)",
     * description="Atualiza os dados de um artigo pelo slug, com opção de alterar publicação e imagens. Substitui imagens da galeria, se enviadas. Requer autenticação.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="slug",
     * in="path",
     * required=true,
     * description="Slug do artigo",
     * @OA\Schema(type="string", example="titulo-do-artigo")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="title", type="string", maxLength=150, example="Título do Artigo Atualizado"),
     * @OA\Property(property="slug", type="string", maxLength=255, example="titulo-do-artigo-atualizado"),
     * @OA\Property(property="excerpt", type="string", maxLength=500, example="Resumo atualizado do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo atualizado do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true, description="Define se o artigo será publicado"),
     * @OA\Property(property="is_featured", type="boolean", example=false, description="Define se o artigo é destacado"),
     * @OA\Property(property="cover_image", type="file", description="Nova imagem de capa (JPEG, PNG, max: 5MB)"),
     * @OA\Property(
     * property="gallery_images[]",
     * type="array",
     * description="Novas imagens da galeria (JPEG, PNG, max: 5MB cada)",
     * @OA\Items(type="file")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Artigo atualizado com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="title", type="string", example="Título do Artigo Atualizado"),
     * @OA\Property(property="slug", type="string", example="titulo-do-artigo-atualizado"),
     * @OA\Property(property="excerpt", type="string", example="Resumo atualizado do artigo"),
     * @OA\Property(property="content", type="string", example="Conteúdo atualizado do artigo"),
     * @OA\Property(property="is_published", type="boolean", example=true),
     * @OA\Property(property="is_featured", type="boolean", example=false),
     * @OA\Property(
     * property="author",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Autor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="author@example.com")
     * ),
     * @OA\Property(
     * property="publisher",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="Editor do Artigo"),
     * @OA\Property(property="email", type="string", format="email", example="publisher@example.com")
     * ),
     * @OA\Property(
     * property="coverImage",
     * type="object",
     * nullable=true,
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="path", type="string", example="articles/1/capa.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=true),
     * @OA\Property(property="sort_order", type="integer", example=0)
     * ),
     * @OA\Property(
     * property="images",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="path", type="string", example="articles/1/img1.jpg"),
     * @OA\Property(property="is_cover", type="boolean", example=false),
     * @OA\Property(property="sort_order", type="integer", example=1)
     * )
     * ),
     * @OA\Property(property="published_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Não autenticado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The title field is required."),
     * @OA\Property(
     * property="errors",
     * type="object",
     * @OA\Property(
     * property="title",
     * type="array",
     * @OA\Items(type="string", example="The title field is required.")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro ao atualizar artigo",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Erro ao atualizar artigo: Falha ao salvar imagem.")
     * )
     * )
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        // Implementação do método update (AdminArticleController)
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/articles/{slug}",
     *     tags={"Artigos Admin"},
     *     summary="Exclui um artigo (admin)",
     *     description="Realiza exclusão lógica (soft delete) de um artigo pelo slug. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug do artigo",
     *         @OA\Schema(type="string", example="titulo-do-artigo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Artigo excluído com sucesso.")
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
     *         description="Artigo não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir artigo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao excluir artigo: Falha no servidor.")
     *         )
     *     )
     * )
     */
    public function destroy(Article $article): JsonResponse
    {
        // Implementação do método destroy (AdminArticleController)
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/articles/{slug}/restore",
     *     tags={"Artigos Admin"},
     *     summary="Restaura um artigo excluído (admin)",
     *     description="Restaura um artigo que foi excluído logicamente (soft delete) pelo slug. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug do artigo",
     *         @OA\Schema(type="string", example="titulo-do-artigo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artigo restaurado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Artigo restaurado.")
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
     *         description="Artigo não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Artigo não encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao restaurar artigo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao restaurar artigo.")
     *         )
     *     )
     * )
     */
    public function restore($slug): JsonResponse
    {
        // Implementação do método restore (AdminArticleController)
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/articles/{slug}/categories",
     *     tags={"Artigos Admin"},
     *     summary="Associa uma categoria a um artigo (admin)",
     *     description="Associa uma categoria existente a um artigo pelo slug, verificando permissões e evitando duplicatas. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug do artigo",
     *         @OA\Schema(type="string", example="titulo-do-artigo")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id"},
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID da categoria a ser associada"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria associada com sucesso ou já associada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoria associada ao artigo com sucesso."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="article_slug", type="string", example="titulo-do-artigo"),
     *                 @OA\Property(property="category_slug", type="string", example="categoria-exemplo")
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
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ação não autorizada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artigo ou categoria não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoria não encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The category_id field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The category_id field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao associar categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao associar a categoria. Tente novamente.")
     *         )
     *     )
     * )
     */
    public function attachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        // Implementação do método attachCategory (AdminArticleController)
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/articles/{slug}/categories",
     *     tags={"Artigos Admin"},
     *     summary="Desassocia uma categoria de um artigo (admin)",
     *     description="Remove a associação de uma categoria de um artigo pelo slug, verificando permissões e existência da associação. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug do artigo",
     *         @OA\Schema(type="string", example="titulo-do-artigo")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id"},
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID da categoria a ser desassociada"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria desassociada com sucesso ou não estava associada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoria desassociada do artigo com sucesso."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="article_slug", type="string", example="titulo-do-artigo"),
     *                 @OA\Property(property="category_slug", type="string", example="categoria-exemplo")
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
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ação não autorizada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artigo ou categoria não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoria não encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The category_id field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The category_id field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao desassociar categoria",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao desassociar a categoria. Tente novamente.")
     *         )
     *     )
     * )
     */
    public function detachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        // Implementação do método detachCategory (AdminArticleController)
    }


}
