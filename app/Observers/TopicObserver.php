<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\Topic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Clears topic- and course-related cache tags when a topic is mutated.
 */
class TopicObserver
{
    /**
     * @param Topic $topic
     *
     * @return void
     */
    public function created(Topic $topic): void
    {
        $this->invalidate($topic);
        Log::channel('db')->info('Topic created', ['topic_id' => $topic->id, 'course_id' => $topic->course_id]);
    }

    /**
     * @param Topic $topic
     *
     * @return void
     */
    public function updated(Topic $topic): void
    {
        $this->invalidate($topic);
    }

    /**
     * @param Topic $topic
     *
     * @return void
     */
    public function deleted(Topic $topic): void
    {
        $this->invalidate($topic);
        Log::channel('db')->info('Topic deleted', ['topic_id' => $topic->id, 'course_id' => $topic->course_id]);
    }

    /**
     * @param Topic $topic
     *
     * @return void
     */
    public function restored(Topic $topic): void
    {
        $this->invalidate($topic);
    }

    /**
     * @param Topic $topic
     *
     * @return void
     */
    public function forceDeleted(Topic $topic): void
    {
        $this->invalidate($topic);
    }

    /**
     * Flush cache tags for topics and the parent course.
     *
     * @param Topic $topic
     *
     * @return void
     */
    private function invalidate(Topic $topic): void
    {
        Cache::tags(['topics', 'courses', "course.{$topic->course_id}"])->flush();
    }
}
