<?php

namespace Modules\Articles\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): JsonResponse
    {
        try {
            // Inicia a query para artigos publicados
            $query = Article::published()
                ->with(['author', 'coverImage', 'images'])
                ->latest('published_at');

            // Aplica filtro opcional por categorias
            if ($request->has('category_ids')) {
                $categoryIds = is_array($request->input('category_ids'))
                    ? $request->input('category_ids')
                    : explode(',', $request->input('category_ids'));

                // Valida que os category_ids são inteiros válidos
                $categoryIds = array_filter($categoryIds, fn($id) => is_numeric($id) && $id > 0);

                if (!empty($categoryIds)) {
                    $query->whereHas('categories', function ($query) use ($categoryIds) {
                        $query->whereIn('categories.id', $categoryIds);
                    });
                }
            }

            // Executa a query com paginação
            $articles = $query->paginate(12);

            // Loga a busca bem-sucedida
            Log::info('Artigos carregados com sucesso', [
                'category_ids' => $request->input('category_ids', []),
                'total' => $articles->total(),
                'user_id' => auth()->id() ?? 'guest',
            ]);

            return response()->json(new ArticleCollection($articles), 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar artigos', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'category_ids' => $request->input('category_ids', []),
                'user_id' => auth()->id() ?? 'guest',
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
