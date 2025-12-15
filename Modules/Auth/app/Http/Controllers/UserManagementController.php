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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\Articles\app\Http\Controllers\Notifications\WelcomeNotificationController;
use Modules\Auth\app\Http\Requests\RegisterRequest;
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

        // Busca global
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        // Ordem: primeiro por prioridade de role, depois por data de criação
        $query->orderByRaw("
            CASE
                WHEN role = 'admin' THEN 1
                WHEN role = 'editor' THEN 2
                WHEN role = 'reader' THEN 3
                ELSE 4
            END
        ");
        $query->orderBy('created_at', 'desc');

        $users = $query->get();
        $total = $users->count();

        return response()->json([
            'search' => $search,
            'status' => $status,
            'total'  => $total,
            'users'  => UserResource::collection($users),
        ], 200);

    } catch (Exception $e) {
        Log::error('Erro ao carregar lista de usuários', [
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

    public function store(RegisterRequest $request): JsonResponse
    {
        // 1. Autorização: Verifica se o usuário autenticado é um admin
        $admin = Auth::user();
        if (!$admin || $admin->role !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado. Apenas administradores podem criar usuários.',
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Cria o usuário
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Permite ao admin definir a 'role'. O RegisterRequest deve validar que esta role é segura.
                'role' => $request->role ?? 'reader',
            ]);

            DB::commit();

            // Opcional: Dispara notificação de boas-vindas
            $user->notify(new WelcomeNotificationController($user));
            // Opcional: event(new UserRegistered($user));

            Log::info('Usuário criado', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Usuário criado com sucesso pelo administrador.',
                'user' => new UserResource($user),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar usuário pelo administrador', [
                'admin_id' => $admin->id,
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao criar usuário. Tente novamente mais tarde.',
            ], 500);
        }
    }

    public function update(Request $request, $userId): JsonResponse
    {
        // 1. Autorização: Verifica se o usuário autenticado é um admin
        $admin = Auth::user();
        if (!$admin || $admin->role !== 'admin') {
            return response()->json([
                'message' => 'Acesso negado. Apenas administradores podem atualizar usuários.',
            ], 403);
        }

        // 2. Busca o Usuário (incluindo desativados para o admin)
        $user = User::withTrashed()->find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        // RECOMENDADO: Substituir o código de validação abaixo por um UpdateUserRequest dedicado.
        $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            // Regra unique ignorando o ID do usuário atual
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'    => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // 'confirmed' requer password_confirmation
            'role'     => ['sometimes', 'required', 'string', Rule::in(['admin', 'editor', 'reader'])], // Ajuste as roles
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['name', 'username', 'email', 'role']);

            // 3. Lógica Condicional de Senha
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // 4. Regra de Segurança: Impede que o Admin rebaixe sua própria role
            if ($user->id === $admin->id && isset($data['role']) && $data['role'] !== 'admin') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Um administrador não pode rebaixar sua própria conta para uma função inferior.',
                ], 403);
            }

            // 5. Execução da Atualização
            $user->update($data);

            DB::commit();

            // Retorna o recurso atualizado
            return response()->json([
                'message' => 'Usuário atualizado com sucesso.',
                'user' => new UserResource($user->fresh()), // fresh() recarrega o modelo com os novos dados
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar usuário pelo administrador', [
                'admin_id' => $admin->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar usuário. Tente novamente mais tarde.',
            ], 500);
        }
    }
}
