<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User\UserCompletedLesson;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates progress-related cache when a user's lesson completion row changes.
 */
class CompletedLessonObserver
{
    /**
     * Handle the UserCompletedLesson "created" event.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    public function created(UserCompletedLesson $userCompletedLesson): void
    {
        $this->invalidate($userCompletedLesson);
    }

    /**
     * Handle the UserCompletedLesson "updated" event.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    public function updated(UserCompletedLesson $userCompletedLesson): void
    {
        $this->invalidate($userCompletedLesson);
    }

    /**
     * Handle the UserCompletedLesson "deleted" event.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    public function deleted(UserCompletedLesson $userCompletedLesson): void
    {
        $this->invalidate($userCompletedLesson);
    }

    /**
     * Handle the UserCompletedLesson "restored" event.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    public function restored(UserCompletedLesson $userCompletedLesson): void
    {
        $this->invalidate($userCompletedLesson);
    }

    /**
     * Handle the UserCompletedLesson "force deleted" event.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    public function forceDeleted(UserCompletedLesson $userCompletedLesson): void
    {
        $this->invalidate($userCompletedLesson);
    }

    /**
     * Flush statistics and per-user progress cache tags.
     *
     * @param UserCompletedLesson $userCompletedLesson
     *
     * @return void
     */
    private function invalidate(UserCompletedLesson $userCompletedLesson): void
    {
        Cache::tags(['statistics', 'progress', "user.{$userCompletedLesson->user_id}.progress"])->flush();
    }
}
