<?php

namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Newsletter",
    description: "Operações de Inscrição, Disparo de Envio e Rastreamento da Newsletter."
)]
class NewsletterAnnotationController extends Controller
{
    // --- 1. POST /api/newsletter/subscribe ---

    /**
     * @OA\Post(
     * path="/api/v1/newsletter/subscribe",
     * tags={"Newsletter"},
     * summary="Inscrever-se na Newsletter",
     * description="Adiciona ou reativa um e-mail na lista de inscritos da Newsletter (Rota Pública).",
     * @OA\RequestBody(
     * required=true,
     * description="Dados do inscrito",
     * @OA\JsonContent(
     * required={"email"},
     * @OA\Property(property="email", type="string", format="email", example="joao.silva@exemplo.com"),
     * @OA\Property(property="name", type="string", example="João Silva", nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Inscrito com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Inscrito com sucesso!"),
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação",
     * ),
     * )
     */
    public function subscribe(): JsonResponse {}

    // --- 2. GET /api/newsletter/unsubscribe/{token} ---

    /**
     * @OA\Get(
     * path="/api/v1/newsletter/unsubscribe/{token}",
     * tags={"Newsletter"},
     * summary="Cancelar Inscrição na Newsletter",
     * description="Cancela a inscrição na Newsletter usando um token de segurança (Rota Pública).",
     * @OA\Parameter(
     * name="token",
     * in="path",
     * description="Token criptografado do ID do inscrito.",
     * required=true,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Cancelado com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Cancelado com sucesso."),
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Link inválido (Token Decryption Error)",
     * ),
     * )
     */
    public function unsubscribe(): JsonResponse {}

    // --- 3. GET /api/newsletter/subscribers ---

    /**
     * @OA\Get(
     * path="/api/v1/admin/newsletter/subscribers",
     * tags={"Newsletter"},
     * summary="Lista de Inscritos",
     * description="Retorna uma lista paginada de todos os inscritos (ativos e inativos). Requer autenticação (Admin).",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="subscribed",
     * in="query",
     * description="Filtra por status de inscrição (true ou false)",
     * required=false,
     * @OA\Schema(type="boolean")
     * ),
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Busca por nome ou e-mail",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Inscritos retornados com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", type="array", @OA\Items(type="object")),
     * @OA\Property(property="meta", type="object"),
     * )
     * ),
     * @OA\Response(response=401, description="Não autenticado"),
     * )
     */
    public function index(): JsonResponse {}

    // --- 4. GET /api/newsletter/subscribers/stats ---

    /**
     * @OA\Get(
     * path="/api/v1/admin/newsletter/subscribers/stats",
     * tags={"Newsletter"},
     * summary="Estatísticas de Inscritos",
     * description="Retorna contagem total de inscritos, ativos, inativos e a taxa de ativos. Requer autenticação (Admin).",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Estatísticas retornadas com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(property="total", type="integer", example=1250),
     * @OA\Property(property="active", type="integer", example=1200),
     * @OA\Property(property="inactive", type="integer", example=50),
     * @OA\Property(property="active_rate", type="number", format="float", example=96.0, description="Taxa percentual de inscritos ativos.")
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Não autenticado"),
     * )
     */
    public function stats(): JsonResponse {}

    // --- 5. POST /api/newsletter/send-article ---

    /**
     * @OA\Post(
     * path="/api/v1/admin/newsletter/send-article",
     * tags={"Newsletter"},
     * summary="Dispara o envio de um Artigo como Newsletter",
     * description="Dispara um evento na fila para enviar um artigo publicado a todos os inscritos. O ID do usuário logado é registrado. Requer autenticação (Admin).",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dados do artigo a ser enviado",
     * @OA\JsonContent(
     * required={"slug"},
     * @OA\Property(property="slug", type="string", example="aplicacao-fullstack-em-minutos", description="O slug do artigo publicado para envio.")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Evento de envio disparado e enfileirado com sucesso",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Evento de envio de Newsletter para o artigo 'Título do Artigo' disparado pelo User ID 1 com sucesso.")
     * )
     * ),
     * @OA\Response(response=401, description="Não autenticado"),
     * @OA\Response(response=404, description="Artigo não encontrado"),
     * @OA\Response(response=500, description="Erro interno ao disparar evento"),
     * )
     */
    public function sendArticleAsNewsletter(): JsonResponse {}

    // --- 6. GET /api/newsletter/sent-articles ---

    /**
     * @OA\Get(
     * path="/api/v1/admin/newsletter/sent-articles",
     * tags={"Newsletter"},
     * summary="Histórico de Artigos Enviados (Logs)",
     * description="Lista os artigos que já foram enviados como Newsletter (logs de disparo), incluindo detalhes de quem disparou o envio e quando. Requer autenticação (Admin).",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Itens por página (default: 20)",
     * required=false,
     * @OA\Schema(type="integer", example=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Logs de envio retornados com sucesso",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="article_id", type="integer", example=42),
     * @OA\Property(property="user_id", type="integer", example=1, description="ID do usuário que disparou o envio."),
     * @OA\Property(property="sent_at", type="string", format="date-time", example="2025-12-11T16:00:00Z"),
     * @OA\Property(
     * property="article",
     * type="object",
     * @OA\Property(property="id", type="integer", example=42),
     * @OA\Property(property="title", type="string", example="Aplicação Fullstack em MINUTOS")
     * ),
     * @OA\Property(
     * property="sender",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Admin Gildo"),
     * @OA\Property(property="email", type="string", example="admin@exemplo.com")
     * )
     * )
     * ),
     * @OA\Property(property="meta", type="object"),
     * )
     * ),
     * @OA\Response(response=401, description="Não autenticado"),
     * )
     */
    public function sentArticlesLog(): JsonResponse {}
}
