<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\CourseReview;
use Illuminate\Support\Facades\Cache;

/**
 * Clears review and course aggregate cache when a course review changes.
 */
class ReviewObserver
{
    /**
     * @param CourseReview $courseReview
     *
     * @return void
     */
    public function created(CourseReview $courseReview): void
    {
        $this->invalidate($courseReview);
    }

    /**
     * @param CourseReview $courseReview
     *
     * @return void
     */
    public function updated(CourseReview $courseReview): void
    {
        $this->invalidate($courseReview);
    }

    /**
     * @param CourseReview $courseReview
     *
     * @return void
     */
    public function deleted(CourseReview $courseReview): void
    {
        $this->invalidate($courseReview);
    }

    /**
     * @param CourseReview $courseReview
     *
     * @return void
     */
    public function restored(CourseReview $courseReview): void
    {
        $this->invalidate($courseReview);
    }

    /**
     * @param CourseReview $courseReview
     *
     * @return void
     */
    public function forceDeleted(CourseReview $courseReview): void
    {
        $this->invalidate($courseReview);
    }

    /**
     * Flush statistics, reviews, and course-scoped cache tags.
     *
     * @param CourseReview $courseReview
     *
     * @return void
     */
    private function invalidate(CourseReview $courseReview): void
    {
        Cache::tags(['statistics', 'reviews', "course.{$courseReview->course_id}"])->flush();
    }
}
