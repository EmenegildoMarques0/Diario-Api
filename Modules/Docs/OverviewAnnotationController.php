<?php

namespace Modules\Docs;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Endpoints para o painel administrativo"
 * )
 */
class OverviewAnnotationController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/admin/dashboard/overview",
     *     tags={"Dashboard"},
     *     summary="Admin dashboard overview",
     *     description="Returns a comprehensive overview of the admin dashboard including article statistics, recent content, charts, and top read articles. Requires admin authentication.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Overview data returned successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="total_articles", type="integer", example=120),
     *                 @OA\Property(property="published_articles", type="integer", example=95),
     *                 @OA\Property(property="draft_articles", type="integer", example=20),
     *                 @OA\Property(property="trashed_articles", type="integer", example=5),
     *                 @OA\Property(property="total_categories", type="integer", example=12),
     *                 @OA\Property(property="categories_with_articles", type="integer", example=10),
     *                 @OA\Property(property="authors", type="integer", example=8),
     *                 @OA\Property(property="total_users", type="integer", example=45)
     *             ),
     *             @OA\Property(
     *                 property="recent_articles",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Aplicação Fullstack em MINUTOS"),
     *                     @OA\Property(property="slug", type="string", example="aplicacao-fullstack-em-minutos"),
     *                     @OA\Property(property="author", type="string", example="John Doe"),
     *                     @OA\Property(property="status", type="string", example="Publicado"),
     *                     @OA\Property(property="status_color", type="string", example="green"),
     *                     @OA\Property(property="updated_at", type="string", example="10 minutes ago"),
     *                     @OA\Property(property="cover_url", type="string", nullable=true, example="https://s3.amazonaws.com/articles/1/cover.png"),
     *                     @OA\Property(property="edit_url", type="string", example="http://127.0.0.1:8000/v1/admin/articles/slug/edit"),
     *                     @OA\Property(property="restore_url", type="string", nullable=true, example=null),
     *                     @OA\Property(property="view_url", type="string", nullable=true, example="http://127.0.0.1:8000/v1/articles/slug")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="recent_categories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Tecnologia"),
     *                     @OA\Property(property="slug", type="string", example="tecnologia"),
     *                     @OA\Property(property="articles_count", type="integer", example=15),
     *                     @OA\Property(property="creator", type="string", example="John Doe"),
     *                     @OA\Property(property="created_at", type="string", example="02/12/2025"),
     *                     @OA\Property(property="edit_url", type="string", example="http://127.0.0.1:8000/v1/admin/categories/tecnologia/edit")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="charts",
     *                 type="object",
     *                 @OA\Property(
     *                     property="articles_per_day",
     *                     type="object",
     *                     @OA\Property(
     *                         property="labels",
     *                         type="array",
     *                         @OA\Items(type="string", example="03/11")
     *                     ),
     *                     @OA\Property(
     *                         property="created",
     *                         type="array",
     *                         @OA\Items(type="integer", example=0)
     *                     ),
     *                     @OA\Property(
     *                         property="read",
     *                         type="array",
     *                         @OA\Items(type="integer", example=45)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="articles_by_category",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="label", type="string", example="Tecnologia"),
     *                         @OA\Property(property="value", type="integer", example=35)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="status_distribution",
     *                     type="object",
     *                     @OA\Property(property="published", type="integer", example=95),
     *                     @OA\Property(property="draft", type="integer", example=20),
     *                     @OA\Property(property="trashed", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="most_read_articles",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Aplicação Fullstack em MINUTOS"),
     *                     @OA\Property(property="slug", type="string", example="aplicacao-fullstack-em-minutos"),
     *                     @OA\Property(property="author", type="string", example="John Doe"),
     *                     @OA\Property(property="views", type="integer", example=1247),
     *                     @OA\Property(property="published_at", type="string", example="02/12/2025"),
     *                     @OA\Property(property="cover_url", type="string", nullable=true),
     *                     @OA\Property(property="view_url", type="string", example="http://127.0.0.1:8000/v1/articles/slug")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User lacks permission",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to load dashboard overview.")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        // Implementation will be in OverviewController
        // This is only for OpenAPI documentation
        return response()->json([]);
    }
}
