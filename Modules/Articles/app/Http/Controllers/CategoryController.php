<?php

namespace Modules\Articles\app\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Articles\app\Http\Requests\CategoryRequest;
use Modules\Articles\app\Models\Category;
use Modules\Articles\app\Transformers\CategoryResource;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $categories = Category::with('creator')->get();
        return response()->json(CategoryResource::collection($categories));
    }


public function store(CategoryRequest $request): JsonResponse
{
    try {
        return DB::transaction(function () use ($request) {
            // obtém apenas os dados validados
            $data = $request->validated();

            // --- GERAR SLUG ÚNICO ---
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;

            // repete até achar um slug livre
            while (DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . rand(0, 5000);
            }

            $data['slug'] = $slug;
            $data['created_by'] = auth()->id();

            // cria a categoria
            $category = Category::create($data);

            // valida se o registro foi criado corretamente
            if (!$category->exists || !$category->id) {
                \Log::error('Falha ao criar categoria', [
                    'user_id' => auth()->id(),
                    'data' => $data,
                ]);
                throw new \Exception('Erro ao criar categoria: ID não gerado.');
            }

            \Log::info('Categoria criada com sucesso', [
                'category_id' => $category->id,
                'user_id' => auth()->id(),
            ]);

            // retorna recurso carregado
            return response()->json(new CategoryResource($category), 201);
        });
    } catch (\Exception $e) {
        \Log::error('Erro ao criar categoria', [
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json(['message' => 'Erro ao criar categoria: ' . $e->getMessage()], 500);
    }
}

    public function show(Category $category): JsonResponse
    {
        return response()->json(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());
        return response()->json(new CategoryResource($category));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();
        return response()->json(['message' => 'Categoria deletada']);
    }
}
