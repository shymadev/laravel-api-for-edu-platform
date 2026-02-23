<?php

declare(strict_types=1);

namespace App\Http\Resources\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Education\Lesson
 */
class LessonResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'title' => $this->title,
            'weight' => $this->weight,
            'content' => $this->content,
            'topic' => $this->whenLoaded('topic'),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
