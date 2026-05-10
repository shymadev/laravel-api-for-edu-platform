<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\Course;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Clears course- and statistics-related cache when a course is mutated.
 */
class CourseObserver
{
    /**
     * @param Course $course
     *
     * @return void
     */
    public function created(Course $course): void
    {
        $this->invalidate($course);
        Log::channel('db')->info('Course created', ['course_id' => $course->id, 'title' => $course->title]);
    }

    /**
     * @param Course $course
     *
     * @return void
     */
    public function updated(Course $course): void
    {
        $this->invalidate($course);
    }

    /**
     * @param Course $course
     *
     * @return void
     */
    public function deleted(Course $course): void
    {
        $this->invalidate($course);
        Log::channel('db')->info('Course deleted', ['course_id' => $course->id]);
    }

    /**
     * @param Course $course
     *
     * @return void
     */
    public function restored(Course $course): void
    {
        $this->invalidate($course);
    }

    /**
     * @param Course $course
     *
     * @return void
     */
    public function forceDeleted(Course $course): void
    {
        $this->invalidate($course);
    }

    /**
     * Flush cache tags for this course and global statistics/lists.
     *
     * @param Course $course
     *
     * @return void
     */
    private function invalidate(Course $course): void
    {
        Cache::tags(['statistics', 'courses', "course.{$course->id}"])->flush();
    }
}
