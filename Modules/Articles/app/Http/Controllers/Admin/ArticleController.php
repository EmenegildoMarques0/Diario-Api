<?php

namespace Modules\Articles\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Articles\app\Http\Requests\StoreArticleRequest;
use Modules\Articles\app\Http\Requests\UpdateArticleRequest;
use Modules\Articles\app\Models\{Article, ArticleImage};
use Modules\Articles\app\Transformers\ArticleCollection;
use Modules\Articles\app\Transformers\ArticleResource;

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
            //dd($request->all());

            $data = $request->except(['cover_image', 'gallery_images']);

            if ($request->has('is_published') && $request->boolean('is_published') && !$article->is_published) {
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

            return response()->json(new ArticleResource(
                $article->load(['author', 'publisher', 'coverImage', 'images'])
            ));
        } catch (Exception $e) {
            \Log::error('Erro ao atualizar artigo', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
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
            \Log::info('Iniciando upload da imagem de capa', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
            ]);

            $oldCover = $article->coverImage;
            if ($oldCover) {
                Storage::disk('public')->delete($oldCover->path);
                $oldCover->delete();
            }

            $path = $file->store("articles/{$article->id}", 'public');

            ArticleImage::create([
                'article_id' => $article->id,
                'path' => $path,
                'is_cover' => true,
                'sort_order' => 0,
            ]);

            \Log::info('Imagem de capa salva com sucesso', [
                'article_id' => $article->id,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            // Remover o arquivo salvo, se existir
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            \Log::error('Erro ao fazer upload da imagem de capa', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Falha ao salvar imagem de capa: ' . $e->getMessage());
        }
    }

    private function uploadGalleryImage(Article $article, $file, $index): void
    {
        try {
            \Log::info('Iniciando upload da imagem da galeria', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'index' => $index,
            ]);

            $path = $file->store("articles/{$article->id}", 'public');

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
            ]);
        } catch (\Exception $e) {
            // Remover o arquivo salvo, se existir
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            \Log::error('Erro ao fazer upload da imagem da galeria', [
                'article_id' => $article->id,
                'file' => $file->getClientOriginalName(),
                'index' => $index,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Falha ao salvar imagem da galeria: ' . $e->getMessage());
        }
    }
}
