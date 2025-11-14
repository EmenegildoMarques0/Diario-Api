<?php
namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Requests\UpdateProfileRequest;
use Modules\Auth\app\Transformers\UserResource;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            return response()->json(new UserResource(auth()->user()));
        } catch (Exception $e) {
            Log::error('Erro ao carregar perfil', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Erro ao carregar perfil.'], 500);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        try {
            return DB::transaction(function () use ($request, $user) {
                // Logar todos os dados recebidos
                Log::info('Requisição recebida', [
                    'user_id' => $user->id,
                    'all_input' => $request->all(),
                    'files' => $request->hasFile('avatar') ? $request->file('avatar')->getClientOriginalName() : null,
                ]);

                $data = $request->validated();

                // Logar dados validados
                Log::info('Dados validados', [
                    'user_id' => $user->id,
                    'data' => $data,
                ]);

                // UPLOAD DE AVATAR (apenas editor/admin)
                if ($request->hasFile('avatar')) {
                    if (!in_array($user->role, ['editor', 'admin'])) {
                        return response()->json([
                            'message' => 'Você não tem permissão para fazer upload de avatar.',
                        ], 403);
                    }
                    $this->uploadAvatar($user, $request->file('avatar'), $data);
                }

                // REMOVE AVATAR (se enviado como null)
                if ($request->has('avatar') && $request->input('avatar') === 'null') {
                    if (in_array($user->role, ['editor', 'admin'])) {
                        if ($user->avatar_url) {
                            $disk = config('filesystems.default', 'public');
                            $oldPath = $user->avatar_url;
                            if (Storage::disk($disk)->exists($oldPath)) {
                                Storage::disk($disk)->delete($oldPath);
                            }
                            $data['avatar_url'] = null;
                        }
                    }
                }

                // Atualiza usuário
                if (!empty($data)) {
                    $user->update($data);
                    $user->refresh();
                } else {
                    Log::warning('Nenhum dado para atualizar', ['user_id' => $user->id]);
                }

                return response()->json([
                    'message' => 'Perfil atualizado com sucesso.',
                    'user' => new UserResource($user),
                ], 200);
            });
        } catch (Exception $e) {
            Log::error('Erro ao atualizar perfil', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Erro ao atualizar perfil. Tente novamente.',
            ], 500);
        }
    }

    private function uploadAvatar($user, $file, &$data): void
    {
        try {
            $disk = config('filesystems.default');
            Log::info('Iniciando upload de avatar', [
                'user_id' => $user->id,
                'file' => $file->getClientOriginalName(),
                'disk' => $disk,
            ]);

            if ($user->avatar_url && Storage::disk($disk)->exists($user->avatar_url)) {
                Storage::disk($disk)->delete($user->avatar_url);
            }

            $path = $file->store("avatars/{$user->id}", $disk);
            $data['avatar_url'] = $path;

            Log::info('Avatar salvo com sucesso', [
                'user_id' => $user->id,
                'path' => $path,
                'disk' => $disk,
            ]);
        } catch (Exception $e) {
            if (isset($path) && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
            Log::error('Erro ao fazer upload de avatar', [
                'user_id' => $user->id,
                'file' => $file->getClientOriginalName(),
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao salvar avatar: ' . $e->getMessage());
        }
    }
}
