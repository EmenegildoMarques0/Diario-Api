<?php
namespace Modules\Docs;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Transformers\UserResource;

/**
 * @OA\Info(
 *     title="API de Autenticação",
 *     version="1.0.0",
 *     description="API para gerenciamento de autenticação de usuários"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter the Bearer token obtained from the login or register endpoint to access protected routes."
 * )
 */
class AuthAnnotationController extends Controller
{
    /**
     * Registro de usuário
     *
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Autenticação"},
     *     summary="Registra um novo usuário",
     *     description="Cria um novo usuário e retorna um token de autenticação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", maxLength=150, example="John Doe", description="Nome do usuário, máximo 150 caracteres"),
     *             @OA\Property(property="username", type="string", example="johndoe", description="Nome de usuário, opcional"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="E-mail único, máximo 255 caracteres"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="password123", description="Senha, mínimo 8 caracteres"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Confirmação da senha"),
     *             @OA\Property(property="role", type="string", example="reader", description="Papel do usuário, opcional, padrão é 'reader'")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="reader"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-04T12:28:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-04T12:28:00Z")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|random_token_here", description="Bearer token for authenticated requests")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao criar usuário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao criar usuário. Tente novamente mais tarde.")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Método para registro de usuário
    }

    /**
     * Login (email ou username)
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Autenticação"},
     *     summary="Autentica um usuário",
     *     description="Realiza login com email ou username e retorna um token de autenticação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", example="john@example.com", description="E-mail ou nome de usuário"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Senha do usuário")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="reader"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-04T12:28:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-04T12:28:00Z")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|random_token_here", description="Bearer token for authenticated requests")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Conta desativada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Esta conta foi desativada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao processar login",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno ao processar login.")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Método para autenticação de usuário
    }

    /**
     * Logout
     *
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Desloga um usuário",
     *     description="Revoga todos os tokens de autenticação do usuário autenticado",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nenhum usuário autenticado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao realizar logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao realizar logout.")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        // Método para deslogar usuário
    }

    /**
     * Validação de token
     *
     * @OA\Get(
     *     path="/api/v1/auth/validate-token",
     *     tags={"Autenticação"},
     *     summary="Verifica a validade do token e retorna os dados do usuário",
     *     description="Verifica se o token fornecido é válido e retorna os dados do usuário",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token válido, dados do usuário retornados",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token válido."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="reader"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-04T12:28:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-04T12:28:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token inválido ou expirado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao validar token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao validar token.")
     *         )
     *     )
     * )
     */
    public function validateToken(Request $request): JsonResponse
    {
        // Método para validar token de autenticação
    }

    /**
     * Enviar e-mail de recuperação de senha
     *
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     tags={"Autenticação"},
     *     summary="Enviar e-mail de recuperação de senha",
     *     description="Envia um e-mail com um link para redefinir a senha do usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="E-mail do usuário, máximo 255 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="E-mail de recuperação enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link de reset enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao enviar e-mail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao enviar link")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        // Método para enviar e-mail de recuperação de senha
    }

    /**
     * Resetar senha do usuário
     *
     * @OA\Post(
     *     path="/api/v1/auth/reset-password",
     *     tags={"Autenticação"},
     *     summary="Resetar senha do usuário",
     *     description="Redefine a senha do usuário usando um token de recuperação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abcdef123456", description="Token de recuperação"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="E-mail do usuário, máximo 255 caracteres"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="NovaSenha123", description="Nova senha, mínimo 8 caracteres"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NovaSenha123", description="Confirmação da nova senha")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha redefinida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Senha redefinida com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao redefinir senha",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao redefinir senha")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        // Método para resetar senha do usuário
    }
}
