<?php

declare(strict_types=1);

namespace App\Http\Resources\Log;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Additional\Log
 */
class LogResource extends JsonResource
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
            'channel' => $this->channel,
            'level' => $this->level,
            'message' => $this->message,
            'context' => $this->context,
            'user_id' => $this->user_id,
            'ip' => $this->ip,
            'url' => $this->url,
            'method' => $this->method,
            'created_at' => $this->created_at,
        ];
    }
}
