<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User\User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role->role_name ?? null,
            'profile_id' => $this->profile_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_blocked' => $this->is_blocked,
            'auth_provider' => $this->google_id !== null ? 'google' : 'local',
            'has_local_password' => $this->hasLocalPassword(),
        ];
    }
}
