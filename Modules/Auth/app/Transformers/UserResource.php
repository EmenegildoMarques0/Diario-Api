<?php

namespace Modules\Auth\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'can_upload_avatar' => in_array($this->role, ['editor', 'admin']),
        ];
    }
}
