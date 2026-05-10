<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Education\CourseReview
 */
class CourseReviewResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'profile' => $this->user->profile ? [
                    'first_name' => $this->user->profile->first_name,
                    'last_name' => $this->user->profile->last_name,
                    'avatar_url' => $this->user->profile->avatar_url,
                ] : null,
            ],
            'rating' => $this->rating,
            'review_text' => $this->review_text,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
