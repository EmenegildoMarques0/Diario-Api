<?php

namespace Modules\Articles\app\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Articles\app\Transformers\NotificationResource;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);
        return response()->json(NotificationResource::collection($notifications));
    }

    public function unread(): JsonResponse
    {
        $unread = auth()->user()->unreadNotifications()->latest()->paginate(15);
        return response()->json(NotificationResource::collection($unread));
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->markAllNotificationsAsRead();
        return response()->json(['message' => 'Todas as notificações marcadas como lidas']);
    }
}
