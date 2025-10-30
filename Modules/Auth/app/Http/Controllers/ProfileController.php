<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Auth\app\Http\Requests\UpdateProfileRequest;
use Modules\Auth\app\Transformers\UserResource;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            return response()->json(new UserResource(auth()->user()));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar perfil.',
            ], 500);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $data = $request->validated();

            // === UPLOAD DE AVATAR (apenas editor/admin) ===
            if ($request->hasFile('avatar')) {
                // Verifica permissão
                if (!in_array($user->role, ['editor', 'admin'])) {
                    return response()->json([
                        'message' => 'Você não tem permissão para fazer upload de avatar.',
                    ], 403);
                }

                $file = $request->file('avatar');

                // Remove avatar antigo (se existir)
                if ($user->avatar_url) {
                    $oldPath = Str::after($user->avatar_url, 'avatars/');
                    if (Storage::disk('public')->exists('avatars/' . $oldPath)) {
                        Storage::disk('public')->delete('avatars/' . $oldPath);
                    }
                }

                // Salva novo avatar
                $path = $file->store('avatars', 'public');
                $data['avatar_url'] = Storage::url($path); // URL pública
            }

            // Remove avatar se for null (limpeza manual)
            if ($request->has('avatar') && $request->input('avatar') === null) {
                if (in_array($user->role, ['editor', 'admin'])) {
                    if ($user->avatar_url) {
                        $oldPath = Str::after($user->avatar_url, 'avatars/');
                        Storage::disk('public')->delete('avatars/' . $oldPath);
                    }
                    $data['avatar_url'] = null;
                }
            }

            // Atualiza usuário
            $user->update($data);

            return response()->json([
                'message' => 'Perfil atualizado com sucesso.',
                'user' => new UserResource($user),
            ], 200);

        } catch (Exception $e) {
            \Log::error('Erro ao atualizar perfil', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar perfil. Tente novamente.',
            ], 500);
        }
    }
}
