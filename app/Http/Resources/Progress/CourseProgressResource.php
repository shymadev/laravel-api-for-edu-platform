<?php

declare(strict_types=1);

namespace App\Http\Resources\Progress;

use App\DTO\Progress\CourseProgressDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseProgressResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray(Request $request): array
    {
        /** @var CourseProgressDTO $this->resource */
        return [
            'course_id' => $this->resource->courseId,
            'title' => $this->resource->title,
            'preview_image_url' => $this->resource->previewImageUrl,
            'total_lessons' => $this->resource->totalLessons,
            'completed_lessons' => $this->resource->completedLessons,
            'progress_percentage' => $this->resource->progressPercentage,
            'is_completed' => $this->resource->isCompleted,
        ];
    }
}
