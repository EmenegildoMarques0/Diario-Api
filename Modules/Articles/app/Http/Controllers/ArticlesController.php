<?php

namespace Modules\Articles\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Articles\app\Models\Article;
use Modules\Articles\app\Transformers\ArticleCollection;
use Modules\Articles\app\Transformers\ArticleResource;
use Throwable;

class ArticlesController extends Controller
{
    /**
     * Retorna a lista de artigos publicados.
     */
    public function index(): JsonResponse
    {
        try {
            $articles = Article::published()
                ->with(['author', 'coverImage', 'images'])
                ->latest('published_at')
                ->paginate(12);

            return response()->json(new ArticleCollection($articles));
        } catch (Throwable $e) {
            Log::error('Erro ao carregar artigos', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao carregar os artigos.',
                'message' => 'Ocorreu um problema ao obter os artigos. Verifique os logs para mais detalhes.',
            ], 500);
        }
    }

    /**
     * Exibe um artigo específico.
     */
    public function show(Article $article): JsonResponse
    {
        try {
            if (!$article->is_published || $article->published_at > now()) {
                abort(404);
            }

            $article->incrementViewCount();

            return response()->json(new ArticleResource(
                $article->load(['author', 'publisher', 'coverImage', 'images'])
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
                'message' => 'Ocorreu um problema ao exibir o artigo. Verifique os logs para mais detalhes.',
            ], 500);
        }
    }
}
