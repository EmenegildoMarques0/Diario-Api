<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Requests\UpdateRoleRequest;
use Modules\Auth\app\Transformers\UserResource;

class UserPromotionController extends Controller
{
    /**
     * Promove ou rebaixa um usuário
     */
    public function updateRole(UpdateRoleRequest $request, $userId): JsonResponse
    {
        try {
            // Verifica se o usuário autenticado é admin
            $admin = Auth::user();
            if (!$admin || $admin->role !== 'admin') {
                return response()->json([
                    'message' => 'Apenas administradores podem alterar roles de usuários.',
                ], 403);
            }

            // Valida o role enviado
            $request->validate([
                'role' => 'required|in:admin,editor,reader',
            ]);

            // Busca o usuário a ser modificado
            $user = User::findOrFail($userId);

            // Impede que um admin altere seu próprio role
            if ($user->id === $admin->id) {
                return response()->json([
                    'message' => 'Você não pode alterar seu próprio role.',
                ], 403);
            }

            // Verifica se o usuário foi soft deleted
            if ($user->trashed()) {
                return response()->json([
                    'message' => 'Não é possível alterar o role de um usuário desativado.',
                ], 403);
            }

            // Verifica se o role já é o mesmo
            if ($user->role === $request->role) {
                return response()->json([
                    'message' => 'O usuário já possui este role.',
                ], 400);
            }

            // Verifica regras de promoção/rebaixamento
            if ($user->role === 'reader' && !in_array($request->role, ['admin', 'editor'])) {
                return response()->json([
                    'message' => 'Usuários reader só podem ser promovidos para admin ou editor.',
                ], 400);
            }
         /*
            if (in_array($user->role, ['admin', 'editor']) && $request->role !== 'reader') {
                return response()->json([
                    'message' => 'Usuários admin ou editor só podem ser rebaixados para reader.',
                ], 400);
            }
        */
            DB::beginTransaction();
            try {
                $user->update([
                    'role' => $request->role,
                ]);
                DB::commit();

                $message = $request->role === 'reader'
                    ? 'Usuário rebaixado com sucesso.'
                    : 'Usuário promovido com sucesso.';

                return response()->json([
                    'message' => $message,
                    'user' => new UserResource($user),
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Erro ao alterar role do usuário', [
                    'user_id' => $userId,
                    'admin_id' => $admin->id,
                    'new_role' => $request->role,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'message' => 'Erro ao alterar role do usuário. Tente novamente mais tarde.',
                ], 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
            ], 404);
        } catch (Exception $e) {
            Log::error('Erro inesperado ao alterar role do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Erro interno ao processar a alteração de role.',
            ], 500);
        }
    }
}
