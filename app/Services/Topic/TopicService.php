<?php

declare(strict_types=1);

namespace App\Services\Topic;

use App\DTO\Topic\CreateTopicDTO;
use App\DTO\Topic\UpdateTopicDTO;
use App\Models\Education\Topic;
use App\Services\Contracts\Topic\TopicServiceInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for handling topic-related business logic.
 */
class TopicService implements TopicServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTopicById(int $id): Topic
    {
        return Topic::with(['lessons'])->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createTopic(CreateTopicDTO $dto): Topic
    {
        return Topic::create($dto->toArray());
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function deleteTopic(Topic $topic): bool
    {
        return (bool) $topic->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicsByCourse(int $courseId): Collection
    {
        return Topic::with(['lessons'])
            ->where('course_id', $courseId)
            ->orderBy('title')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Topic $topic): Topic
    {
        $topic->update(['is_active' => true]);
        $topic->save();

        return $topic->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(Topic $topic): Topic
    {
        $topic->update(['is_active' => false]);
        $topic->save();

        return $topic->refresh();
    }
}
