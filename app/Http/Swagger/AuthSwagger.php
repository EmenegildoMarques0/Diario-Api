<?php

namespace App\Http\Swagger;


/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API de Autenticação",
 *     description="Documentação Swagger da API do módulo Auth"
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Operações de autenticação de usuários"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API Token"
 * )
 */

/**
 * User Schema
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Emenegildo Marques"),
 *     @OA\Property(property="username", type="string", example="emenegildo"),
 *     @OA\Property(property="email", type="string", example="example@mail.com"),
 *     @OA\Property(property="role", type="string", example="reader"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T12:00:00Z")
 * )
 */

/**
 * RegisterRequest Schema
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name","email","password","password_confirmation"},
 *     @OA\Property(property="name", type="string", example="Emenegildo Marques"),
 *     @OA\Property(property="username", type="string", example="emenegildo"),
 *     @OA\Property(property="email", type="string", format="email", example="example@mail.com"),
 *     @OA\Property(property="password", type="string", format="password", example="12345678"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="12345678"),
 *     @OA\Property(property="role", type="string", example="reader")
 * )
 */

/**
 * LoginRequest Schema
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"login","password"},
 *     @OA\Property(property="login", type="string", example="example@mail.com"),
 *     @OA\Property(property="password", type="string", format="password", example="12345678")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/register",
 *     tags={"Auth"},
 *     summary="Registrar um novo usuário",
 *     description="Cria um novo usuário e retorna um token de autenticação",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Usuário criado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso."),
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *             @OA\Property(property="token", type="string", example="token_gerado_pelo_sanctum")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro ao criar usuário",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erro ao criar usuário. Tente novamente mais tarde.")
 *         )
 *     )
 * )
 */

/**
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Login do usuário",
 *     description="Autentica um usuário usando email ou username e retorna um token",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login realizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Login realizado com sucesso."),
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *             @OA\Property(property="token", type="string", example="token_gerado_pelo_sanctum")
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

/**
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Auth"},
 *     summary="Logout do usuário",
 *     description="Revoga todos os tokens do usuário autenticado",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout realizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Nenhum usuário autenticado",
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

/**
 * @OA\Get(
 *     path="/api/validate-token",
 *     tags={"Auth"},
 *     summary="Valida token de autenticação",
 *     description="Verifica se o token do usuário ainda é válido",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Token válido",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Token válido."),
 *             @OA\Property(property="user", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Token inválido ou expirado",
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
