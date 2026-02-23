<?php

declare(strict_types=1);

namespace App\Http\Resources\Progress;

use App\DTO\Progress\UserStatisticsDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatisticsResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray(Request $request): array
    {
        /** @var UserStatisticsDTO $this->resource */
        return [
            'total_completed_lessons' => $this->resource->totalCompletedLessons,
            'total_completed_courses' => $this->resource->totalCompletedCourses,
            'total_in_progress_courses' => $this->resource->totalInProgressCourses,
            'average_progress' => $this->resource->averageProgress,
        ];
    }
}
