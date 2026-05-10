<?php

declare(strict_types=1);

namespace App\Http\Resources\Topic;

use App\Models\Education\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Topic
 */
class TopicResource extends JsonResource
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
            'course_id' => $this->course_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'is_active' => $this->is_active,
            'lesson_count' => $this->whenLoaded('lessons', fn () => $this->lessons->count(), 0),
        ];
    }
}
