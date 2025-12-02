<?php

namespace Modules\Dashboard\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Articles\app\Models\{Article, Category};
use App\Models\User;

class OverviewController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe o overview do painel admin.
     */
    public function show(): JsonResponse
    {
        $this->authorize('viewAny', Article::class);

        $data = Cache::remember('articles_admin_overview_v6', 300, function () {
            return [
                'summary'            => $this->getSummary(),
                'recent_articles'    => $this->getRecentArticles(),
                'recent_categories'  => $this->getRecentCategories(),
                'charts'             => $this->getChartsData(),
                'most_read_articles' => $this->getMostReadArticles(),
            ];
        });

        return response()->json($data);
    }

    /* --------------------------------------------------------------
     * SUMMARY – COM total_users
     * ------------------------------------------------------------ */
    private function getSummary(): array
    {
        $totalArticles = Article::withTrashed()->count();
        $published     = Article::whereNotNull('published_at')->count();
        $draft         = Article::whereNull('published_at')->count(); // Corrigido: removi whereNotNull('created_at')
        $trashed       = Article::onlyTrashed()->count();

        return [
            'total_articles'          => $totalArticles,
            'published_articles'      => $published,
            'draft_articles'          => $draft,
            'trashed_articles'        => $trashed,
            'total_categories'        => Category::count(),
            'categories_with_articles'=> Category::has('articles')->count(),
            'authors'                 => Article::distinct('author_id')->count('author_id'),
            'total_users'             => User::count(),
        ];
    }

    /* --------------------------------------------------------------
     * RECENT ARTICLES
     * ------------------------------------------------------------ */
    private function getRecentArticles(): array
    {
        return Article::with(['author', 'coverImage'])
            ->withTrashed()
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($article) {
                $status = $article->trashed()
                    ? 'Excluído'
                    : ($article->published_at ? 'Publicado' : 'Rascunho');

                $statusColor = $article->trashed()
                    ? 'red'
                    : ($article->published_at ? 'green' : 'yellow');

                return [
                    'id'           => $article->id,
                    'title'        => $article->title,
                    'slug'         => $article->slug,
                    'author'       => $article->author?->name ?? 'Desconhecido',
                    'status'       => $status,
                    'status_color' => $statusColor,
                    'updated_at'   => $article->updated_at->diffForHumans(),
                    'cover_url'    => $article->coverImage?->url ?? null,
                    'edit_url'     => url("/v1/admin/articles/{$article->slug}/edit"),
                    'restore_url'  => $article->trashed()
                        ? url("/v1/admin/articles/{$article->slug}/restore")
                        : null,
                    'view_url'     => $article->published_at
                        ? url("/v1/articles/{$article->slug}")
                        : null,
                ];
            })->toArray();
    }

    /* --------------------------------------------------------------
     * RECENT CATEGORIES
     * ------------------------------------------------------------ */
    private function getRecentCategories(): array
    {
        return Category::withCount('articles')
            ->with('creator')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id'              => $category->id,
                    'name'            => $category->name,
                    'slug'            => $category->slug,
                    'articles_count'  => $category->articles_count,
                    'creator'         => $category->creator?->name ?? 'Sistema',
                    'created_at'      => $category->created_at->format('d/m/Y'),
                    'edit_url'        => url("/v1/admin/categories/{$category->slug}/edit"),
                ];
            })->toArray();
    }

    /* --------------------------------------------------------------
     * CHARTS DATA – COM LEITURAS POR DIA (corrigido: views_count + número)
     * ------------------------------------------------------------ */
    private function getChartsData(): array
    {
        $last30Days = Carbon::now()->subDays(30);

        // === ARTIGOS CRIADOS POR DIA ===
        $articlesPerDay = Article::withTrashed()
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // === LEITURAS POR DIA (SOMA DE views_count) – CORRIGIDO
        $readsPerDay = DB::table('articles')
            ->whereNotNull('published_at')
            ->where('updated_at', '>=', $last30Days)
            ->selectRaw('DATE(updated_at) as date, COALESCE(SUM(view_count), 0) as total_reads')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_reads', 'date')
            ->map(fn($value) => (int) $value) // FORÇA NÚMERO
            ->toArray();

        // Preenche os 30 dias
        $dates   = [];
        $created = [];
        $read    = [];

        for ($i = 29; $i >= 0; $i--) {
            $date   = Carbon::now()->subDays($i)->format('Y-m-d');
            $label  = Carbon::parse($date)->format('d/m');

            $dates[]   = $label;
            $created[] = $articlesPerDay[$date] ?? 0;
            $read[]    = $readsPerDay[$date] ?? 0;
        }

        // Top 8 categorias
        $articlesByCategory = Category::withCount('articles')
            ->orderByDesc('articles_count')
            ->take(8)
            ->get()
            ->map(fn($cat) => [
                'label' => $cat->name,
                'value' => $cat->articles_count,
            ])->toArray();

        // Distribuição de status
        $statusDistribution = [
            'published' => Article::whereNotNull('published_at')->count(),
            'draft'     => Article::whereNull('published_at')->count(),
            'trashed'   => Article::onlyTrashed()->count(),
        ];

        return [
            'articles_per_day' => [
                'labels'   => $dates,
                'created'  => $created,
                'read'     => $read,
            ],
            'articles_by_category' => $articlesByCategory,
            'status_distribution'  => $statusDistribution,
        ];
    }

    /* --------------------------------------------------------------
     * MOST READ ARTICLES – CORRIGIDO: views_count e ?? 0
     * ------------------------------------------------------------ */
    private function getMostReadArticles(): array
    {
        return Article::published()
            ->with(['author', 'coverImage'])
            ->orderByDesc('view_count')
            ->take(5)
            ->get()
            ->map(function ($article) {
                return [
                    'id'           => $article->id,
                    'title'        => $article->title,
                    'slug'         => $article->slug,
                    'author'       => $article->author?->name ?? 'Desconhecido',
                    'views'        => $article->views_count ?? 0, // CORRIGIDO: número, nunca null
                    'published_at' => $article->published_at?->format('d/m/Y'),
                    'cover_url'    => $article->coverImage?->url ?? null,
                    'view_url'     => url("/v1/articles/{$article->slug}"),
                ];
            })->toArray();
    }
}
