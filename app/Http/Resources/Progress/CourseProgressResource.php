<?php

declare(strict_types=1);

namespace App\Http\Resources\Progress;

use App\DTO\Progress\CourseProgressDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for course progress (DTO-backed).
 */
class CourseProgressResource extends JsonResource
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
        /** @var CourseProgressDTO $resource */
        $resource = $this->resource;

        return [
            'course_id' => $resource->courseId,
            'title' => $resource->title,
            'preview_image_url' => $resource->previewImageUrl,
            'total_lessons' => $resource->totalLessons,
            'completed_lessons' => $resource->completedLessons,
            'progress_percentage' => $resource->progressPercentage,
            'is_completed' => $resource->isCompleted,
            'is_stopped' => $resource->isStopped,
        ];
    }
}
