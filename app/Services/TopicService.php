<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Topic\CreateTopicDTO;
use App\DTO\Topic\UpdateTopicDTO;
use App\Models\Education\Topic;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service for handling topic-related business logic.
 */
#[Singleton]
class TopicService
{
    /**
     * @param int $id
     *
     * @return Topic
     */
    public function getTopicById(int $id): Topic
    {
        return Cache::tags(['topics', "topic.{$id}"])->remember(
            "topic.{$id}",
            1800,
            fn () => Topic::with(['lessons'])->findOrFail($id),
        );
    }

    /**
     * @param CreateTopicDTO $dto
     *
     * @return Topic
     */
    public function createTopic(CreateTopicDTO $dto): Topic
    {
        $createdTopic = Topic::create($dto->toArray());
        $createdTopic->is_active = false;
        $createdTopic->save();

        return $createdTopic;
    }

    /**
     * @param Topic $topic
     * @param UpdateTopicDTO $dto
     *
     * @return Topic
     */
    public function updateTopic(Topic $topic, UpdateTopicDTO $dto): Topic
    {
        $data = $dto->toArray();
        unset($data['id']);

        $topic->update($data);
        $topic->refresh();

        return $topic;
    }

    /**
     * @param Topic $topic
     *
     * @return boolean
     */
    public function deleteTopic(Topic $topic): bool
    {
        return (bool) $topic->delete();
    }

    /**
     * @param int $courseId
     *
     * @return Collection<int, Topic>
     */
    public function getTopicsByCourse(int $courseId): Collection
    {
        return Cache::tags(['topics', "course.{$courseId}"])->remember(
            "topics.course.{$courseId}",
            1800,
            fn () => Topic::with(['lessons'])
                ->where('course_id', $courseId)
                ->orderBy('title')
                ->get(),
        );
    }

    /**
     * @param Topic $topic
     *
     * @return Topic
     */
    public function publish(Topic $topic): Topic
    {
        $topic->update(['is_active' => true]);
        $topic->save();

        return $topic->refresh();
    }

    /**
     * @param Topic $topic
     *
     * @return Topic
     */
    public function unpublish(Topic $topic): Topic
    {
        $topic->update(['is_active' => false]);
        $topic->save();

        return $topic->refresh();
    }
}
