<?php

declare(strict_types=1);

namespace App\Http\Resources\Progress;

use App\DTO\Progress\UserStatisticsDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for aggregate user learning statistics.
 */
class UserStatisticsResource extends JsonResource
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
        /** @var UserStatisticsDTO $resource */
        $resource = $this->resource;

        return [
            'total_completed_lessons' => $resource->totalCompletedLessons,
            'total_completed_courses' => $resource->totalCompletedCourses,
            'total_in_progress_courses' => $resource->totalInProgressCourses,
            'average_progress' => $resource->averageProgress,
            'total_completed_exercises' => $resource->totalCompletedExercises,
            'total_listened_audio' => $resource->totalListenedAudio,
        ];
    }
}
