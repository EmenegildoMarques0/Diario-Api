<?php

namespace Modules\Articles\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Articles\app\Http\Requests\StoreArticleRequest;
use Modules\Articles\app\Http\Requests\UpdateArticleRequest;
use Modules\Articles\app\Http\Requests\AttachCategoryRequest;
use Modules\Articles\app\Models\{Article, ArticleImage, Category};
use Modules\Articles\app\Transformers\ArticleCollection;
use Modules\Articles\app\Transformers\ArticleResource;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Notifications\ArticlePublishedByAdminNotification;
use Modules\Articles\app\Traits\CacheManagementTrait;

class ArticleController extends Controller
{
    use AuthorizesRequests, CacheManagementTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $articles = Article::with(['author', 'publisher', 'coverImage', 'categories'])
            ->latest('published_at')
            ->paginate(20);

        return response()->json(new ArticleCollection($articles));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {

                // Obter todos os dados, exceto as imagens
                $data = $request->except(['cover_image', 'gallery_images']);

                // CORREÇÃO ESSENCIAL: Garante que os booleanos sejam true/false de PHP
                $isPublished = $request->boolean('is_published', false);
                $isFeatured = $request->boolean('is_featured', false);

                $data['is_published'] = $isPublished;
                $data['is_featured'] = $isFeatured;

                if ($isPublished) {
                    $data['published_at'] = now();
                    $data['published_by'] = auth()->id();
                } else {
                    $data['published_at'] = null; // Garante que não publica se is_published for false
                    $data['published_by'] = null;
                }

                $article = Article::create($data);

                if (!$article->exists) {
                    throw new \Exception('Erro ao criar artigo: ID não gerado.');
                }

                Log::info('Artigo criado com sucesso', [
                    'article_i d' => $article->id,
                    'user_id' => auth()->id(),
                ]);

                // Upload da imagem de capa
                if ($request->hasFile('cover_image')) {
                    $this->uploadCoverImage($article, $request->file('cover_image'));
                }

                // Upload das imagens da galeria
                if ($request->hasFile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $index => $file) {
                        $this->uploadGalleryImage($article, $file, $index);
                    }
                }

                $this->flushArticlesCache();


                // Dispara evento se publicado
                if ($article->published_at) {
                    event(new ArticlePublished($article));
                }

                return response()->json(new ArticleResource(
                    $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
                ), 201);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao criar artigo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao criar artigo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json(new ArticleResource(
            $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        try {
            $this->authorize('update', $article);

            Log::debug('Dados recebidos no Update:', $request->all());

            return DB::transaction(function () use ($request, $article) {
                // Obter todos os dados, exceto as imagens
                $data = $request->except(['cover_image', 'gallery_images']);
                Log::debug('Dados Validados (após validated()):', $data);
                // CORREÇÃO ESSENCIAL: Garante que os booleanos sejam true/false de PHP
                // Usamos $article->is_published como default caso o campo não seja enviado no update (PUT/PATCH)
                $isPublished = $request->boolean('is_published', $article->is_published);
                $isFeatured = $request->boolean('is_featured', $article->is_featured);

                $data['is_published'] = $isPublished;
                $data['is_featured'] = $isFeatured;

                $wasJustPublished = $isPublished && !$article->is_published;

                if ($wasJustPublished) {
                    $data['published_at'] = now();
                    $data['published_by'] = auth()->id();
                } elseif (!$isPublished && $article->is_published) {
                    // Se o artigo estava publicado e agora não está
                    $data['published_at'] = null;
                    $data['published_by'] = null;
                }
                // Se $isPublished == $article->is_published, 'published_at' não é alterado

                $article->update($data);

                // Atualiza capa
                if ($request->hasFile('cover_image')) {
                    $this->uploadCoverImage($article, $request->file('cover_image'));
                }

                // Atualiza galeria (substitui todas)
                if ($request->hasFile('gallery_images')) {
                    $article->images()->where('is_cover', false)->delete();
                    foreach ($request->file('gallery_images') as $index => $file) {
                        $this->uploadGalleryImage($article, $file, $index);
                    }
                }

                // Notificação para autor quando admin publica
                if ($wasJustPublished && auth()->user()->role === 'admin' && $article->author_id !== auth()->id()) {
                    $author = $article->author;
                    if ($author) {
                        $author->notify(new ArticlePublishedByAdminNotification($article, auth()->user()));
                        Log::info('Notificação enviada ao autor', [
                            'article_id' => $article->id,
                            'author_id' => $author->id,
                        ]);
                    }
                }

                $this->flushArticlesCache();

                return response()->json(new ArticleResource(
                    $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
                ));
            });
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar artigo', [
                'article_id' => $article->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao atualizar artigo.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): JsonResponse
    {
        try {
            $this->authorize('delete', $article);

            $article->delete();

            Log::info('Artigo excluído com sucesso', [
                'article_id' => $article->id,
                'user_id' => auth()->id(),
            ]);

            $this->flushArticlesCache();

            return response()->json(['message' => 'Artigo excluído com sucesso.'], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir artigo', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao excluir artigo.'], 500);
        }
    }

    /**
     * Restore a soft deleted article.
     */
    public function restore($id): JsonResponse
    {
        try {
            $article = Article::withTrashed()->findOrFail($id);
            $this->authorize('restore', $article);

            $article->restore();

            Log::info('Artigo restaurado com sucesso', ['article_id' => $id]);
                $this->flushArticlesCache();

            return response()->json(['message' => 'Artigo restaurado com sucesso.']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar artigo', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao restaurar artigo.'], 500);
        }
    }

    /**
     * Attach a category to the article.
     */
    public function attachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        try {
            $this->authorize('update', $article);

            $category = Category::findOrFail($request->validated('category_id'));

            return DB::transaction(function () use ($article, $category) {
                if ($article->categories()->where('category_id', $category->id)->exists()) {
                    return response()->json([
                        'message' => 'A categoria já está associada ao artigo.'
                    ], 200);
                }

                $article->categories()->attach($category->id);

                $this->flushArticlesCache();


                return response()->json([
                    'message' => 'Categoria associada com sucesso.',
                    'article' => new ArticleResource(
                        $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
                    )
                ], 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao associar categoria', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao associar categoria.'], 500);
        }
    }

    /**
     * Detach a category from the article.
     */
    public function detachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        try {
            $this->authorize('update', $article);

            $category = Category::findOrFail($request->validated('category_id'));

            return DB::transaction(function () use ($article, $category) {
                if (!$article->categories()->where('category_id', $category->id)->exists()) {
                    return response()->json([
                        'message' => 'A categoria não está associada ao artigo.'
                    ], 200);
                }

                $article->categories()->detach($category->id);

                $this->flushArticlesCache();


                return response()->json([
                    'message' => 'Categoria desassociada com sucesso.',
                    'article' => new ArticleResource(
                        $article->load(['author', 'publisher', 'coverImage', 'images', 'categories'])
                    )
                ], 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao desassociar categoria', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao desassociar categoria.'], 500);
        }
    }

    // ===================================================================
    // Métodos privados de upload
    // ===================================================================

    private function uploadCoverImage(Article $article, $file): void
    {
        $disk = config('filesystems.default');

        try {
            $oldCover = $article->coverImage;

            if ($oldCover && Storage::disk($disk)->exists($oldCover->path)) {
                Storage::disk($disk)->delete($oldCover->path);
                $oldCover->delete();
            }

            $path = $file->store("articles/{$article->id}", $disk);

            ArticleImage::create([
                'article_id' => $article->id,
                'path' => $path,
                'is_cover' => true,
                'sort_order' => 0,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Falha ao salvar imagem de capa: ' . $e->getMessage());
        }
    }

    private function uploadGalleryImage(Article $article, $file, $index): void
    {
        $disk = config('filesystems.default');

        try {
            $path = $file->store("articles/{$article->id}", $disk);

            ArticleImage::create([
                'article_id' => $article->id,
                'path' => $path,
                'is_cover' => false,
                'sort_order' => $index + 1,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Falha ao salvar imagem da galeria: ' . $e->getMessage());
        }
    }
}
