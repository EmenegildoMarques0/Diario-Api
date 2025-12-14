<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password; // Adicionado para recuperação de senha
use Exception;
use Illuminate\Http\Request;
use Modules\Articles\app\Events\UserRegistered;
use Modules\Articles\app\Http\Controllers\Notifications\WelcomeNotificationController;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\app\Http\Requests\ResetPasswordRequest;
use Modules\Auth\app\Transformers\UserResource;

class AuthController extends Controller
{
    /**
     * Registro de usuário
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'reader',
            ]);

            DB::commit();

            // Dispara notificação/evento dentro da transação (se preferir, pode disparar após commit)
            $user->notify(new WelcomeNotificationController($user));
            // Se houver um event a disparar:
            // event(new UserRegistered($user));


            // Cria token após commit para garantir consistência
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuário criado com sucesso.',
                'user' => new UserResource($user),
                'token' => $token,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

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

            // Atualiza último login dentro de uma transação simples
            DB::beginTransaction();
            try {
                $user->updateLastLogin();
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::warning('Falha ao atualizar last login, prosseguindo com login', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Não interrompe o fluxo de login por causa de falha nessa atualização
            }

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

            DB::beginTransaction();
            try {
                $user->tokens()->delete();
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Erro ao deletar tokens no logout', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Erro ao realizar logout.',
                ], 500);
            }

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

    /**
     * Solicita o envio de e-mail de redefinição de senha.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // Usa o Password Broker do Laravel para criar um token e enviar o e-mail
            $response = Password::broker()->sendResetLink(
                $request->only('email')
            );

            //

            // Retorna sucesso se o link foi enviado (mesmo que o e-mail não exista, para não revelar e-mails)
            if ($response == Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'O link de redefinição de senha foi enviado para o seu e-mail.',
                ], 200);
            }

            // Caso o broker retorne outro status (como e-mail não encontrado, 'passwords.user'),
            // retornamos uma mensagem de erro genérica.
            return response()->json([
                'message' => 'Não foi possível enviar o link de redefinição. Por favor, verifique o e-mail.',
            ], 404);

        } catch (Exception $e) {
            Log::error('Erro ao solicitar redefinição de senha', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro interno ao solicitar redefinição de senha.',
            ], 500);
        }
    }

    /**
     * Redefine a senha do usuário usando o token de redefinição.
     * Requer um ResetPasswordRequest que valida 'email', 'password' (e confirmação) e 'token'.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),

            // Usando a sintaxe sem type hint 'User' (recomendado)
            function ($user, string $password) {

                // VERIFICAÇÃO DE SEGURANÇA (GUARD CLAUSE)
                if (!$user) {
                    // Se por algum motivo o broker falhou e chamou o callback,
                    // evitamos o erro fatal.
                    return;
                }

                $user->password = Hash::make($password);
                $user->setRememberToken(null);
                $user->save();
            }
        );

        //

        if ($response == Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Sua senha foi redefinida com sucesso.',
            ], 200);
        }

        // Este bloco DEVE ser executado se o token for inválido.
        $message = 'Erro ao redefinir a senha. O token pode ser inválido ou ter expirado.';

        return response()->json([
            'message' => $message,
            'status_code' => $response, // Retorna o código de status do broker para debug
        ], 400);
    }


    public function validateToken(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Token inválido ou expirado.',
                ], 401);
            }

            return response()->json([
                'message' => 'Token válido.',
                'user' => new UserResource($user),
            ], 200);
        } catch (Exception $e) {
            Log::error('Erro ao validar token', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Erro ao validar token.',
            ], 500);
        }
    }
}
