<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User\UserCompletedLesson;
use Illuminate\Support\Facades\Cache;

class CompletedLessonObserver
{
    /**
     * Handle the UserCompletedLesson "created" event.
     */
    public function created(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the UserCompletedLesson "updated" event.
     */
    public function updated(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the UserCompletedLesson "deleted" event.
     */
    public function deleted(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the UserCompletedLesson "restored" event.
     */
    public function restored(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the UserCompletedLesson "force deleted" event.
     */
    public function forceDeleted(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
