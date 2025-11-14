<?php
namespace Modules\Docs;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\app\Http\Requests\UpdateProfileRequest;

class ProfileAnnotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/auth/profile",
     *     tags={"Perfil"},
     *     summary="Exibe o perfil do usuário autenticado",
     *     description="Retorna os detalhes do perfil do usuário autenticado. Requer autenticação.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil retornado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="bio", type="string", nullable=true, example="Desenvolvedor e entusiasta de tecnologia."),
     *                 @OA\Property(property="avatar_url", type="string", nullable=true, example="avatars/1/profile.jpg"),
     *                 @OA\Property(property="role", type="string", example="editor"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao carregar perfil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao carregar perfil.")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
    }

    /**
     * @OA\Put(
     *     path="/api/v1/auth/profile",
     *     tags={"Perfil"},
     *     summary="Atualiza o perfil do usuário autenticado",
     *     description="Atualiza os dados do perfil do usuário autenticado, incluindo nome, username, email, bio e avatar (apenas para editores e administradores). Requer autenticação. Suporta envio de dados como JSON ou multipart/form-data.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do perfil a serem atualizados. Escolha entre JSON ou multipart/form-data (necessário para upload de arquivo).",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", maxLength=150, example="John Doe", description="Nome do usuário"),
     *                 @OA\Property(property="username", type="string", maxLength=80, example="johndoe", description="Nome de usuário único"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="Email único"),
     *                 @OA\Property(property="bio", type="string", maxLength=5000, nullable=true, example="Desenvolvedor e entusiasta de tecnologia.", description="Biografia do usuário")
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", maxLength=150, example="John Doe", description="Nome do usuário"),
     *                 @OA\Property(property="username", type="string", maxLength=80, example="johndoe", description="Nome de usuário único"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com", description="Email único"),
     *                 @OA\Property(property="bio", type="string", maxLength=5000, nullable=true, example="Desenvolvedor e entusiasta de tecnologia.", description="Biografia do usuário"),
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="file",
     *                     description="Imagem do avatar (JPEG, PNG, JPG, GIF, WEBP, máx: 2MB). Obrigatório para editores/administradores se enviado, opcional para outros."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="bio", type="string", nullable=true, example="Desenvolvedor e entusiasta de tecnologia."),
     *                 @OA\Property(property="avatar_url", type="string", nullable=true, example="avatars/1/profile.jpg"),
     *                 @OA\Property(property="role", type="string", example="editor"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-05T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-05T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Ação não autorizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para fazer upload de avatar.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The avatar field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="array",
     *                     @OA\Items(type="string", example="The avatar is required for editors and administrators.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar perfil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar perfil. Tente novamente.")
     *         )
     *     )
     * )
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
    }
}
