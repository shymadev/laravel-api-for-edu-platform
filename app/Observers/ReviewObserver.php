<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\CourseReview;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    /**
     * Handle the CourseReview "created" event.
     */
    public function created(CourseReview $courseReview): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the CourseReview "updated" event.
     */
    public function updated(CourseReview $courseReview): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the CourseReview "deleted" event.
     */
    public function deleted(CourseReview $courseReview): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the CourseReview "restored" event.
     */
    public function restored(CourseReview $courseReview): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the CourseReview "force deleted" event.
     */
    public function forceDeleted(CourseReview $courseReview): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
