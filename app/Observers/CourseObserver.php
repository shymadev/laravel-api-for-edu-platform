<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\Course;
use Illuminate\Support\Facades\Cache;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
