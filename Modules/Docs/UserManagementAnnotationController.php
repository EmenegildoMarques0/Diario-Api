<?php

namespace Modules\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\app\Models\User;

class UserManagementAnnotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/auth/users",
     *     tags={"Usuários Admin"},
     *     summary="Lista todos os usuários em uma única lista (sem agrupar por role)",
     *     description="Retorna todos os usuários (ativos e desativados) em uma lista plana, ordenados por prioridade de role (admin > editor > reader) e data de criação (mais recente primeiro). Suporta filtros de busca e status. Requer autenticação e papel de admin.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Busca por nome, email ou username",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtra por status: all (padrão), active, trashed",
     *         required=false,
     *         @OA\Schema(type="string", enum={"all", "active", "trashed"}, example="all")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários retornada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="search", type="string", nullable=true, example="john"),
     *             @OA\Property(property="status", type="string", example="all"),
     *             @OA\Property(property="total", type="integer", example=15),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe 2"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="admin"),
     *                     @OA\Property(property="bio", type="string", nullable=true, example="Teste de bio"),
     *                     @OA\Property(property="avatar_url", type="string", nullable=true, example=null),
     *                     @OA\Property(property="can_upload_avatar", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated."))),
     *     @OA\Response(response=403, description="Acesso negado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Acesso negado. Apenas administradores."))),
     *     @OA\Response(response=500, description="Erro interno", @OA\JsonContent(@OA\Property(property="message", type="string", example="Erro ao carregar usuários.")))
     * )
     */
    public function index(): JsonResponse { /* ... */ }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/users/{userId}",
     *     tags={"Usuários Admin"},
     *     summary="Exibe um usuário específico",
     *     description="Retorna os dados de um usuário pelo ID, incluindo contas desativadas. Requer autenticação e papel de admin.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer", example=42)),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário retornado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=42),
     *                 @OA\Property(property="name", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *                 @OA\Property(property="username", type="string", example="joaosilva"),
     *                 @OA\Property(property="role", type="string", example="editor"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated."))),
     *     @OA\Response(response=403, description="Acesso negado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Acesso negado."))),
     *     @OA\Response(response=404, description="Usuário não encontrado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Usuário não encontrado."))),
     *     @OA\Response(response=500, description="Erro interno", @OA\JsonContent(@OA\Property(property="message", type="string", example="Erro ao carregar usuário.")))
     * )
     */
    public function show($userId): JsonResponse { /* ... */ }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/users/{userId}",
     *     tags={"Usuários Admin"},
     *     summary="Desativa (soft delete) um usuário",
     *     description="Remove tokens e desativa a conta. Não permite desativar a própria conta.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer", example=42)),
     *     @OA\Response(response=200, description="Conta desativada", @OA\JsonContent(@OA\Property(property="message", type="string", example="Conta desativada com sucesso."))),
     *     @OA\Response(response=400, description="Já desativado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Conta já desativada."))),
     *     @OA\Response(response=401, description="Não autenticado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated."))),
     *     @OA\Response(response=403, description="Acesso negado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Você não pode desativar sua própria conta."))),
     *     @OA\Response(response=404, description="Não encontrado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Usuário não encontrado."))),
     *     @OA\Response(response=500, description="Erro interno", @OA\JsonContent(@OA\Property(property="message", type="string", example="Erro ao desativar conta.")))
     * )
     */
    public function destroy($userId): JsonResponse { /* ... */ }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/users/{userId}/restore",
     *     tags={"Usuários Admin"},
     *     summary="Reativa um usuário desativado",
     *     description="Restaura uma conta previamente desativada.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer", example=42)),
     *     @OA\Response(
     *         response=200,
     *         description="Conta reativada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Conta reativada com sucesso."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=42),
     *                 @OA\Property(property="name", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *                 @OA\Property(property="username", type="string", example="joaosilva"),
     *                 @OA\Property(property="role", type="string", example="editor"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-26T10:00:00Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated."))),
     *     @OA\Response(response=403, description="Acesso negado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Acesso negado."))),
     *     @OA\Response(response=404, description="Não encontrado ou já ativo", @OA\JsonContent(@OA\Property(property="message", type="string", example="Usuário não encontrado ou já ativo."))),
     *     @OA\Response(response=500, description="Erro interno", @OA\JsonContent(@OA\Property(property="message", type="string", example="Erro ao reativar conta.")))
     * )
     */
    public function restore($userId): JsonResponse { /* ... */ }
}
