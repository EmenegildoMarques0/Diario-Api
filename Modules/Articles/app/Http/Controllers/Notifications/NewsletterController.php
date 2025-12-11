<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // Importado
use Exception;
use Modules\Articles\app\Models\NewsletterSubscriber;
use Modules\Articles\app\Models\Article;
use Modules\Articles\app\Models\ArticleNewsletterLog;
use Modules\Articles\app\Events\ArticleReadyForNewsletter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; // Importação útil para o tipo de retorno do cache

class NewsletterController extends Controller
{
    // 1. POST /api/newsletter/subscribe
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name'  => 'nullable|string|max:100',
        ]);

        try {
            $subscriber = NewsletterSubscriber::updateOrCreate(
                ['email' => $request->email],
                [
                    'name'          => $request->name,
                    'is_subscribed' => true,
                ]
            );

            // INVALIDE O CACHE DE ESTATÍSTICAS
            Cache::forget('newsletter_stats');

            return response()->json([
                'success' => true,
                'message' => 'Inscrito com sucesso!',
                'data'    => $subscriber
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro subscribe: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor.'
            ], 500);
        }
    }

    // 2. GET /api/newsletter/unsubscribe/{token}
    public function unsubscribe($token)
    {
        try {
            $id = decrypt($token);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Link inválido.'], 400);
        }

        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);
            $subscriber->update(['is_subscribed' => false]);

            // INVALIDE O CACHE DE ESTATÍSTICAS
            Cache::forget('newsletter_stats');

            return response()->json([
                'success' => true,
                'message' => 'Cancelado com sucesso.',
                'data'    => ['email' => $subscriber->email]
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
        }
    }

    // 3. GET /api/newsletter/subscribers
    public function index(Request $request)
    {
        try {
            $query = NewsletterSubscriber::query();

            if ($request->has('subscribed')) {
                $query->where('is_subscribed', filter_var($request->subscribed, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }

            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            $perPage = $request->get('per_page', 20);
            $subscribers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $subscribers->items(),
                'meta'    => [
                    'current_page' => $subscribers->currentPage(),
                    'last_page'    => $subscribers->lastPage(),
                    'per_page'     => $subscribers->perPage(),
                    'total'        => $subscribers->total(),
                    'from'         => $subscribers->firstItem(),
                    'to'           => $subscribers->lastItem(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao listar subscribers: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar lista.'
            ], 500);
        }
    }

    // 4. GET /api/newsletter/subscribers/stats
    public function stats()
    {
        $cacheKey = 'newsletter_stats';
        $cacheDuration = now()->addMinutes(60); // Cache por 60 minutos

        try {
            // Usa Cache::remember para buscar os dados do cache ou do DB
            $stats = Cache::remember($cacheKey, $cacheDuration, function () {
                $total = NewsletterSubscriber::count();
                $active = NewsletterSubscriber::where('is_subscribed', true)->count();
                $inactive = NewsletterSubscriber::where('is_subscribed', false)->count();

                return [
                    'total'       => $total,
                    'active'      => $active,
                    'inactive'    => $inactive,
                    'active_rate' => $total > 0 ? round(($active / $total) * 100, 1) : 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro nas estatísticas.'], 500);
        }
    }

    /**
     * 5. POST /api/newsletter/send-article
     * Recebe o slug, verifica a existência do artigo, autentica o usuário e dispara o Evento.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendArticleAsNewsletter(Request $request)
    {
        // --- 1. AUTENTICAÇÃO E VALIDAÇÃO ---

        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Acesso não autorizado. É necessário estar logado para disparar a Newsletter.'], 401);
        }

        $userId = auth()->id();

        $request->validate([
            'slug' => 'required|string|max:255',
        ]);

        $slug = $request->slug;

        try {
            // 2. Busca e Verificação do Artigo
            $article = Article::where('slug', $slug)->published()->first();

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => "Artigo com o slug '{$slug}' não encontrado ou não publicado."
                ], 404);
            }

            // --- 3. DISPARO DO EVENTO ---

            // Invalida o cache da primeira página dos logs, pois um novo item será adicionado
            Cache::forget('newsletter_logs_page_1_perpage_20');

            event(new ArticleReadyForNewsletter($article, $userId));

            Log::info("Evento ArticleReadyForNewsletter disparado pelo User ID {$userId} para o artigo: {$article->title}");

            return response()->json([
                'success' => true,
                'message' => "Evento de envio de Newsletter para o artigo '{$article->title}' disparado pelo User ID {$userId} com sucesso."
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao disparar Newsletter: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar disparar o envio.'
            ], 500);
        }
    }

    /**
     * 6. GET /api/newsletter/sent-articles
     * Lista todos os artigos que já foram enviados como Newsletter,
     * incluindo quem disparou o envio (sender) e os detalhes do artigo.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sentArticlesLog(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Acesso não autorizado.'], 401);
        }

        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        // CHAVE DE CACHE ÚNICA POR PAGINAÇÃO
        $cacheKey = "newsletter_logs_page_{$page}_perpage_{$perPage}";
        $cacheDuration = now()->addMinutes(30); // Cache por 30 minutos

        try {
            // Usa Cache::remember para buscar os dados do cache ou do DB
            $logs = Cache::remember($cacheKey, $cacheDuration, function () use ($perPage) {
                return ArticleNewsletterLog::with(['article:id,title', 'sender:id,name,email'])
                            ->orderBy('sent_at', 'desc')
                            ->paginate($perPage);
            });

            // Segurança: Garante que o objeto retornado é um Paginator
            if (!$logs instanceof LengthAwarePaginator) {
                 // Se houver um problema com o objeto de cache, recarrega do banco de dados (sem cache)
                 $logs = ArticleNewsletterLog::with(['article:id,title', 'sender:id,name,email'])
                            ->orderBy('sent_at', 'desc')
                            ->paginate($perPage);
            }

            return response()->json([
                'success' => true,
                'data'    => $logs->items(),
                'meta'    => [
                    'current_page' => $logs->currentPage(),
                    'last_page'    => $logs->lastPage(),
                    'per_page'     => $logs->perPage(),
                    'total'        => $logs->total(),
                    'from'         => $logs->firstItem(),
                    'to'           => $logs->lastItem(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao listar logs de Newsletter: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar o histórico de envios.'
            ], 500);
        }
    }
}
