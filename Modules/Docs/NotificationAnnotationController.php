<?php

namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class NotificationAnnotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/auth/notifications",
     *     tags={"Notificações"},
     *     summary="Lista todas as notificações do usuário autenticado",
     *     description="Retorna uma lista paginada de todas as notificações do usuário autenticado, ordenadas da mais recente para a mais antiga. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página para paginação",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificações retornadas com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="type", type="string", example="App\\Notifications\\ArticlePublished"),
     *                     @OA\Property(property="notifiable_id", type="integer", example=1),
     *                     @OA\Property(property="notifiable_type", type="string", example="App\\Models\\User"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="message", type="string", example="Seu artigo foi publicado!"),
     *                         @OA\Property(property="article_id", type="integer", example=1, nullable=true)
     *                     ),
     *                     @OA\Property(property="read_at", type="string", format="date-time", example="2025-11-05T12:00:00Z", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="total", type="integer", example=50),
     *             @OA\Property(property="last_page", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao carregar notificações",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao carregar notificações.")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/notifications/unread",
     *     tags={"Notificações"},
     *     summary="Lista notificações não lidas do usuário autenticado",
     *     description="Retorna uma lista paginada de notificações não lidas do usuário autenticado, ordenadas da mais recente para a mais antiga. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página para paginação",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificações não lidas retornadas com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="type", type="string", example="App\\Notifications\\ArticlePublished"),
     *                     @OA\Property(property="notifiable_id", type="integer", example=1),
     *                     @OA\Property(property="notifiable_type", type="string", example="App\\Models\\User"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="message", type="string", example="Seu artigo foi publicado!"),
     *                         @OA\Property(property="article_id", type="integer", example=1, nullable=true)
     *                     ),
     *                     @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="total", type="integer", example=10),
     *             @OA\Property(property="last_page", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao carregar notificações não lidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao carregar notificações não lidas.")
     *         )
     *     )
     * )
     */
    public function unread(): JsonResponse
    {
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/auth/notifications/{id}/read",
     *     tags={"Notificações"},
     *     summary="Marca uma notificação como lida",
     *     description="Marca uma notificação específica do usuário autenticado como lida. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da notificação",
     *         required=true,
     *         @OA\Schema(type="string", example="123e4567-e89b-12d3-a456-426614174000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificação marcada como lida",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notificação marcada como lida")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificação não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notificação não encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao marcar notificação como lida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao marcar notificação como lida.")
     *         )
     *     )
     * )
     */
    public function markAsRead(string $id): JsonResponse
    {
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/auth/notifications/read-all",
     *     tags={"Notificações"},
     *     summary="Marca todas as notificações como lidas",
     *     description="Marca todas as notificações do usuário autenticado como lidas. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Todas as notificações marcadas como lidas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Todas as notificações marcadas como lidas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao marcar todas as notificações como lidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao marcar todas as notificações como lidas.")
     *         )
     *     )
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
    }
}
