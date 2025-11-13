<?php

namespace Modules\Articles\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */

    public function toArray($request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'message' => $notification->data['message'] ?? 'Sem mensagem',
            'action_url' => $notification->data['action_url'] ?? null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }

}
