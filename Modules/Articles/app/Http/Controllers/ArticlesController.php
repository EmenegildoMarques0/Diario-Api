<?php

namespace Modules\Articles\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Models\Article;
use Modules\Articles\app\Transformers\ArticleCollection;
use Modules\Articles\app\Transformers\ArticleResource;
use Throwable;

class ArticlesController extends Controller
{
    /**
     * Lista todos os artigos publicados (com paginação e cache inteligente).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Monta chave única baseada nos filtros e página
            $categoryInput = $request->input('category_ids');
            $categoryIds = 'all';

            if ($categoryInput) {
                $ids = is_array($categoryInput)
                    ? $categoryInput
                    : explode(',', $categoryInput);

                $ids = array_filter($ids, fn($id) => is_numeric($id) && $id > 0);
                $categoryIds = !empty($ids) ? implode('_', $ids) : 'none';
            }

            $page = $request->get('page', 1);
            $cacheKey = "articles_index_cat_{$categoryIds}_page_{$page}";

            $articles = Cache::remember($cacheKey, now()->addMinutes(8), function () use ($request) {
                $query = Article::published()
                    ->with(['author', 'coverImage', 'images', 'categories'])
                    ->latest('published_at');

                // Filtro por categorias — CORRIGIDO AQUI
                if ($request->has('category_ids')) {
                    $categoryIds = is_array($request->input('category_ids'))
                        ? $request->input('category_ids')
                        : explode(',', $request->input('category_ids'));

                    $categoryIds = array_filter($categoryIds, fn($id) => is_numeric($id) && $id > 0);

                    if (!empty($categoryIds)) {
                        $query->whereHas('categories', function ($q) use ($categoryIds) {
                            $q->whereIn('categories.id', $categoryIds);
                        });
                    }
                }

                return $query->paginate(12);
            });

            Log::info('Artigos carregados com sucesso', [
                'category_ids' => $categoryIds,
                'page' => $page,
                'total' => $articles->total(),
                'from_cache' => Cache::has($cacheKey),
                'user_id' => auth()->id() ?? 'guest',
            ]);

            return response()->json(new ArticleCollection($articles), 200);

        } catch (\Throwable $e) {
            Log::error('Erro ao carregar artigos', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'guest',
            ]);

            return response()->json([
                'error' => 'Erro ao carregar os artigos.',
                'message' => 'Ocorreu um problema interno. Tente novamente mais tarde.',
            ], 500);
        }
    }

    /**
     * Retorna apenas os artigos destacados (is_featured = true).
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->integer('limit', 6);

            $articles = Cache::remember("featured_articles_limit_{$limit}", now()->addMinutes(15), function () use ($limit) {
                return Article::published()
                    ->featured()
                    ->with(['author', 'coverImage', 'images', 'categories'])
                    ->latest('published_at')
                    ->limit($limit)
                    ->get();
            });

            return response()->json([
                'data' => ArticleResource::collection($articles),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Erro ao carregar artigos em destaque', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar artigos em destaque.',
            ], 500);
        }
    }

    /**
     * Exibe um artigo específico pelo slug.
     */
    public function show(Article $article): JsonResponse
    {
        try {
            if (!$article->is_published || $article->published_at > now()) {
                abort(404);
            }

            $article->incrementViewCount();
            //event(new ArticlePublished($article));

            return response()->json(new ArticleResource(
                $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
            ));

        } catch (Throwable $e) {
            Log::error('Erro ao exibir artigo', [
                'article_id' => $article->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao exibir o artigo.',
                'message' => 'Ocorreu um problema ao exibir o artigo.',
            ], 500);
        }
    }
}
