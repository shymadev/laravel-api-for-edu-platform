<?php

declare(strict_types=1);

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Education\Course
 */
class CourseResource extends JsonResource
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
            'language' => $this->language,
            'title' => $this->title,
            'description' => $this->description,
            'difficulty_level_id' => $this->difficulty_level_id,
            'difficulty_level' => $this->whenLoaded('difficultyLevel', function () {
                return [
                    'id' => $this->difficultyLevel->id,
                    'name' => $this->difficultyLevel->name,
                    'value' => $this->difficultyLevel->value,
                    'description' => $this->difficultyLevel->description,
                ];
            }),
            'preview_image_url' => $this->preview_image,
            'topics' => $this->whenLoaded('topics'),
            'created_at' => $this->created_at,
            'is_premium' => $this->is_premium,
            'is_active' => $this->is_active,
            'is_archived' => $this->is_archived,
        ];
    }
}
