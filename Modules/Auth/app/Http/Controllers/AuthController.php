<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Jobs\SendWelcomeEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Transformers\UserResource;

class AuthController extends Controller
{
    /**
     * Registro de usuário
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'reader',
            ]);


            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuário criado com sucesso.',
                'user' => new UserResource($user),
                'token' => $token,
            ], 201);

        } catch (Exception $e) {
            Log::error('Erro ao registrar usuário', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao criar usuário. Tente novamente mais tarde.',
            ], 500);
        }
    }

    /**
     * Login (email ou username)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $credentials = [
                $field => $request->login,
                'password' => $request->password,
            ];

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Credenciais inválidas.',
                ], 401);
            }

            $user = Auth::user();

            // Verifica se o usuário foi soft deleted
            if ($user->trashed()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Esta conta foi desativada.',
                ], 403);
            }

            $user->updateLastLogin();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login realizado com sucesso.',
                'user' => new UserResource($user),
                'token' => $token,
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao fazer login', [
                'login' => $request->login,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro interno ao processar login.',
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Nenhum usuário autenticado.',
                ], 401);
            }

            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logout realizado com sucesso.',
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao fazer logout', [
                'user_id' => Auth::id() ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro ao realizar logout.',
            ], 500);
        }
    }
}
