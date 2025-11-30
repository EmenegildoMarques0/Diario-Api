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
use Modules\Articles\app\Models\{Article, ArticleImage, Category};
use Modules\Articles\app\Transformers\ArticleCollection;
use Modules\Articles\app\Transformers\ArticleResource;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Modules\Articles\app\Events\ArticlePublished;
use Modules\Articles\app\Http\Controllers\Notifications\ArticlePublishedByAdminNotification;
use Modules\Articles\app\Http\Requests\AttachCategoryRequest;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with(['author', 'publisher', 'coverImage'])
            ->withTrashed()
            ->paginate(20);

        return response()->json(new ArticleCollection($articles));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('articles::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->except(['cover_image', 'gallery_images']);

                if ($request->boolean('is_published')) {
                    $data['published_at'] = now();
                    $data['published_by'] = auth()->id();
                }

                $article = Article::create($data);

                // Verificar se o artigo foi criado com sucesso
                if (!$article->exists || !$article->id) {
                    \Log::error('Falha ao criar artigo', [
                        'user_id' => auth()->id(),
                        'data' => $data,
                    ]);
                    throw new \Exception('Erro ao criar artigo: ID não gerado.');
                }

                \Log::info('Artigo criado com sucesso', [
                    'article_id' => $article->id,
                    'user_id' => auth()->id(),
                ]);

                // === UPLOAD COVER ===
                if ($request->hasFile('cover_image')) {
                    $this->uploadCoverImage($article, $request->file('cover_image'));
                }

                // === UPLOAD GALERIA ===
                if ($request->hasFile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $index => $file) {
                        $this->uploadGalleryImage($article, $file, $index);
                    }
                }

                // Dispara evento se o artigo for publicado
                if ($article->published_at) {
                    event(new ArticlePublished($article));
                }

                // Commit implícito ao sair do bloco de transação
                return response()->json(new ArticleResource(
                    $article->load(['author', 'publisher', 'coverImage', 'images'])
                ), 201);
            });
        } catch (\Exception $e) {
            // Rollback automático pelo DB::transaction em caso de exceção
            \Log::error('Erro ao criar artigo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao criar artigo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json(new ArticleResource(
            $article->load(['author', 'publisher', 'coverImage', 'images'])
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('articles::edit');
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(UpdateArticleRequest $request, Article $article): JsonResponse
{
    try {
        $this->authorize('update', $article);

        return DB::transaction(function () use ($request, $article) {
            $data = $request->except(['cover_image', 'gallery_images']);

            // Verifica se está sendo publicado agora
            $wasJustPublished = $request->has('is_published')
                && $request->boolean('is_published')
                && !$article->is_published;

            if ($wasJustPublished) {
                $data['published_at'] = now();
                $data['published_by'] = auth()->id();
            }

            $article->update($data);

            // === ATUALIZAR COVER ===
            if ($request->hasFile('cover_image')) {
                $this->uploadCoverImage($article, $request->file('cover_image'));
            }

            // === ATUALIZAR GALERIA ===
            if ($request->hasFile('gallery_images')) {
                $article->images()->where('is_cover', false)->delete();
                foreach ($request->file('gallery_images') as $index => $file) {
                    $this->uploadGalleryImage($article, $file, $index);
                }
            }

            // === NOTIFICAÇÃO: Admin publicou artigo de outro autor ===
            if ($wasJustPublished && auth()->user()->role === 'admin' && $article->author_id !== auth()->id()) {
                $author = $article->author;

                if ($author) {
                    // Notificação dentro da transação → se falhar, tudo reverte
                    $author->notify(new ArticlePublishedByAdminNotification($article, auth()->user()));

                    \Log::info('Notificação enviada com sucesso', [
                        'article_id' => $article->id,
                        'author_id' => $author->id,
                        'admin_id' => auth()->id(),
                    ]);
                }
            }

            return response()->json(new ArticleResource(
                $article->load(['author', 'publisher', 'coverImage', 'images'])
            ));
        });

    } catch (Exception $e) {
        \Log::error('Erro ao atualizar artigo (transação revertida)', [
            'article_id' => $article->id ?? null,
            'user_id' => auth()->id() ?? null,
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

            \Log::info('Artigo excluído com sucesso', [
                'article_id' => $article->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'Artigo excluído com sucesso.'], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning('Usuário não autorizado a excluir artigo', [
                'article_id' => $article->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['message' => 'Ação não autorizada.'], 403);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir artigo', [
                'article_id' => $article->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro ao excluir artigo: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $article = Article::withTrashed()->findOrFail($id);

            $this->authorize('restore', $article);

            $article->restore();

            \Log::info("Artigo restaurado com sucesso.", ['article_id' => $id]);

            return response()->json(['message' => 'Artigo restaurado.']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning("Tentativa não autorizada de restaurar artigo.", ['article_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Ação não autorizada.'], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Artigo não encontrado para restauração.", ['article_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Artigo não encontrado.'], 404);
        } catch (\Exception $e) {
            \Log::error("Erro ao restaurar artigo.", ['article_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao restaurar artigo.'], 500);
        }
    }

    private function uploadCoverImage(Article $article, $file): void
    {
        try {
            $disk = config('filesystems.default'); // usa o valor de FILESYSTEM_DISK do .env
            \Log::info('Iniciando upload da imagem de capa', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'disk' => $disk,
            ]);

            // Remove imagem antiga, se existir
            $oldCover = $article->coverImage;
            if ($oldCover) {
                if (Storage::disk($disk)->exists($oldCover->path)) {
                    Storage::disk($disk)->delete($oldCover->path);
                }
                $oldCover->delete();
            }

            // Faz o upload no disco atual (pode ser local ou S3)
            $path = $file->store("articles/{$article->id}", $disk);

            // Salva no banco
            ArticleImage::create([
                'article_id' => $article->id,
                'path' => $path,
                'is_cover' => true,
                'sort_order' => 0,
            ]);

            \Log::info('Imagem de capa salva com sucesso', [
                'article_id' => $article->id,
                'path' => $path,
                'disk' => $disk,
            ]);
        } catch (\Exception $e) {
            // Em caso de erro, remove o arquivo salvo
            if (isset($path) && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            \Log::error('Erro ao fazer upload da imagem de capa', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Falha ao salvar imagem de capa: ' . $e->getMessage());
        }
    }

    private function uploadGalleryImage(Article $article, $file, $index): void
    {
        $disk = config('filesystems.default'); // pega o disco do .env (public ou s3)

        try {
            \Log::info('Iniciando upload da imagem da galeria', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'index' => $index,
                'disk' => $disk,
            ]);

            // Faz o upload no disco configurado
            $path = $file->store("articles/{$article->id}", $disk);

            // Salva no banco
            ArticleImage::create([
                'article_id' => $article->id,
                'path' => $path,
                'is_cover' => false,
                'sort_order' => $index + 1,
            ]);

            \Log::info('Imagem da galeria salva com sucesso', [
                'article_id' => $article->id,
                'path' => $path,
                'index' => $index,
                'disk' => $disk,
            ]);
        } catch (\Exception $e) {
            // Remove arquivo salvo, caso exista
            if (isset($path) && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            \Log::error('Erro ao fazer upload da imagem da galeria', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'index' => $index,
                'disk' => $disk,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Falha ao salvar imagem da galeria: ' . $e->getMessage());
        }
    }

    public function attachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        try {
            // Verifica permissão para atualizar o artigo
            $this->authorize('update', $article);



            // Busca a categoria
            $category = Category::findOrFail($request->validated('category_id'));

            // Inicia uma transação
            return DB::transaction(function () use ($article, $category) {
                // Verifica se a categoria já está associada
                if ($article->categories()->where('category_id', $category->id)->exists()) {
                    Log::info('Tentativa de associar categoria já existente', [
                        'article_slug' => $article->slug,
                        'category_id' => $category->id,
                        'user_id' => auth()->id(),
                    ]);

                    return response()->json([
                        'message' => 'A categoria já está associada ao artigo.',
                        'data' => [
                            'article_slug' => $article->slug,
                            'category_slug' => $category->slug,
                        ]
                    ], 200);
                }

                // Associa a categoria
                $article->categories()->attach($category->id);

                Log::info('Categoria associada ao artigo com sucesso', [
                    'article_slug' => $article->slug,
                    'category_id' => $category->id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Categoria associada ao artigo com sucesso.',
                    'data' => [
                        'article_slug' => $article->slug,
                        'category_slug' => $category->slug,
                    ]
                ], 200);
            });
        } catch (QueryException $e) {
            Log::error('Erro ao associar categoria ao artigo', [
                'article_slug' => $article->slug,
                'category_id' => $request->input('category_id'),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao associar a categoria. Tente novamente.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao associar categoria', [
                'article_slug' => $article->slug,
                'category_id' => $request->input('category_id'),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocorreu um erro inesperado. Tente novamente.',
            ], 500);
        }
    }

    public function detachCategory(AttachCategoryRequest $request, Article $article): JsonResponse
    {
        try {
            // Verifica permissão para atualizar o artigo
            $this->authorize('update', $article);

            // Obtém a categoria validada
            $category = Category::findOrFail($request->validated('category_id'));

            // Inicia uma transação
            return DB::transaction(function () use ($article, $category) {
                // Verifica se a categoria está associada
                if (!$article->categories()->where('category_id', $category->id)->exists()) {
                    Log::info('Tentativa de desassociar categoria não associada', [
                        'article_slug' => $article->slug,
                        'category_id' => $category->id,
                        'user_id' => auth()->id(),
                    ]);

                    return response()->json([
                        'message' => 'A categoria não está associada ao artigo.',
                        'data' => [
                            'article_slug' => $article->slug,
                            'category_slug' => $category->slug,
                        ]
                    ], 200);
                }

                // Desassocia a categoria
                $article->categories()->detach($category->id);

                Log::info('Categoria desassociada do artigo com sucesso', [
                    'article_slug' => $article->slug,
                    'category_id' => $category->id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Categoria desassociada do artigo com sucesso.',
                    'data' => [
                        'article_slug' => $article->slug,
                        'category_slug' => $category->slug,
                    ]
                ], 200);
            });
        } catch (QueryException $e) {
            Log::error('Erro ao desassociar categoria do artigo', [
                'article_slug' => $article->slug,
                'category_id' => $request->validated('category_id', null),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao desassociar a categoria. Tente novamente.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro inesperado ao desassociar categoria', [
                'article_slug' => $article->slug,
                'category_id' => $request->validated('category_id', null),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocorreu um erro inesperado. Tente novamente.',
            ], 500);
        }
    }
}
