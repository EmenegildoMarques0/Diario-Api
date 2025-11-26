<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Auth\app\Transformers\UserResource;

class UserManagementController extends Controller
{
    /**
     * Lista TODOS os usuários agrupados por role
     * Única rota → resposta organizada e pronta para o frontend
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $admin = Auth::user();
            if (!$admin || $admin->role !== 'admin') {
                return response()->json([
                    'message' => 'Acesso negado. Apenas administradores.',
                ], 403);
            }

            $search = $request->query('search');
            $status = $request->query('status', 'all'); // all, active, trashed

            // Base query
            $query = User::query();

            // Filtro de status
            if ($status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($status === 'trashed') {
                $query->onlyTrashed();
            }
            // 'all' → sem filtro (padrão)

            // Busca global
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('username', 'LIKE', "%{$search}%");
                });
            }

            // Busca todos os usuários (com ou sem soft delete)
            $users = $query->orderBy('role')
                           ->orderBy('created_at', 'desc')
                           ->get();

            // Agrupa por role
            $grouped = $users->groupBy('role');

            // Monta resposta estruturada
            $roles = ['admin', 'editor', 'reader'];
            $response = [];

            $totalAll = 0;

            foreach ($roles as $role) {
                $list = $grouped->get($role, collect());
                $count = $list->count();

                $totalAll += $count;

                $response[$role] = [
                    'total' => $count,
                    'users' => UserResource::collection($list),
                ];
            }

            return response()->json([
                'search'  => $search,
                'status'  => $status,
                'total'   => $totalAll,
                'grouped' => $response,
                // Exemplo de estrutura:
                // "grouped" => [
                //   "admin"  => [ "total" => 2, "users" => [...] ],
                //   "editor" => [ "total" => 5, "users" => [...] ],
                //   "reader" => [ "total" => 148, "users" => [...] ],
                // ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao carregar lista agrupada de usuários', [
                'admin_id' => Auth::id() ?? 'unknown',
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao carregar usuários.',
            ], 500);
        }
    }

    // Os outros métodos permanecem iguais (show, destroy, restore)
    // Você pode mantê-los exatamente como na versão anterior

    public function show($userId): JsonResponse
    {
        try {
            if (!Auth::user()?->role === 'admin') {
                return response()->json(['message' => 'Acesso negado.'], 403);
            }

            $user = User::withTrashed()->find($userId);
            if (!$user) return response()->json(['message' => 'Usuário não encontrado.'], 404);

            return response()->json([
                'data'    => new UserResource($user),
                'is_active' => !$user->trashed(),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao exibir usuário', ['user_id' => $userId]);
            return response()->json(['message' => 'Erro ao carregar usuário.'], 500);
        }
    }

    public function destroy($userId): JsonResponse
    {
        try {
            $admin = Auth::user();
            if (!$admin || $admin->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);
            if ($admin->id == $userId) return response()->json(['message' => 'Você não pode desativar sua própria conta.'], 403);

            $user = User::find($userId);
            if (!$user) return response()->json(['message' => 'Usuário não encontrado.'], 404);
            if ($user->trashed()) return response()->json(['message' => 'Conta já desativada.'], 400);

            DB::transaction(function () use ($user) {
                $user->tokens()->delete();
                $user->delete();
            });

            Log::info('Usuário desativado (soft delete)', ['user_id' => $user->id, 'admin_id' => $admin->id]);

            return response()->json(['message' => 'Conta desativada com sucesso.']);
        } catch (Exception $e) {
            Log::error('Erro ao desativar usuário', ['user_id' => $userId]);
            return response()->json(['message' => 'Erro ao desativar conta.'], 500);
        }
    }

    public function restore($userId): JsonResponse
    {
        try {
            if (!Auth::user()?->role === 'admin') return response()->json(['message' => 'Acesso negado.'], 403);

            $user = User::onlyTrashed()->find($userId);
            if (!$user) return response()->json(['message' => 'Usuário não encontrado ou já ativo.'], 404);

            $user->restore();

            Log::info('Usuário reativado', ['user_id' => $user->id, 'admin_id' => Auth::id()]);

            return response()->json([
                'message' => 'Conta reativada com sucesso.',
                'user'    => new UserResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao reativar usuário', ['user_id' => $userId]);
            return response()->json(['message' => 'Erro ao reativar conta.'], 500);
        }
    }
}
