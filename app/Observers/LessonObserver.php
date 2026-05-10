<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\Lesson;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Clears lesson- and topic-related cache tags when a lesson is mutated.
 */
class LessonObserver
{
    /**
     * @param Lesson $lesson
     *
     * @return void
     */
    public function created(Lesson $lesson): void
    {
        $this->invalidate($lesson);
        Log::channel('db')->info('Lesson created', ['lesson_id' => $lesson->id, 'topic_id' => $lesson->topic_id]);
    }

    /**
     * @param Lesson $lesson
     *
     * @return void
     */
    public function updated(Lesson $lesson): void
    {
        $this->invalidate($lesson);
    }

    /**
     * @param Lesson $lesson
     *
     * @return void
     */
    public function deleted(Lesson $lesson): void
    {
        $this->invalidate($lesson);
        Log::channel('db')->info('Lesson deleted', ['lesson_id' => $lesson->id, 'topic_id' => $lesson->topic_id]);
    }

    /**
     * @param Lesson $lesson
     *
     * @return void
     */
    public function restored(Lesson $lesson): void
    {
        $this->invalidate($lesson);
    }

    /**
     * @param Lesson $lesson
     *
     * @return void
     */
    public function forceDeleted(Lesson $lesson): void
    {
        $this->invalidate($lesson);
    }

    /**
     * Flush cache tags for this lesson and its topic.
     *
     * @param Lesson $lesson
     *
     * @return void
     */
    private function invalidate(Lesson $lesson): void
    {
        Cache::tags(['lessons', 'topics', "lesson.{$lesson->id}", "topic.{$lesson->topic_id}"])->flush();
    }
}
