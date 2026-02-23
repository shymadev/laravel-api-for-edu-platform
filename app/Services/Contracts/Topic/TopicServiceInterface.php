<?php

declare(strict_types=1);

namespace App\Services\Contracts\Topic;

use App\DTO\Topic\CreateTopicDTO;
use App\DTO\Topic\UpdateTopicDTO;
use App\Models\Education\Course;
use App\Models\Education\Topic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Interface for Topic service operations.
 */
interface TopicServiceInterface
{
    /**
     * Get a topic by its ID.
     *
     * @param int $id
     *
     * @throws ModelNotFoundException
     *
     * @return Topic
     */
    public function getTopicById(int $id): Topic;

    /**
     * Create a new topic.
     *
     * @param CreateTopicDTO $dto
     *
     * @return Topic
     */
    public function createTopic(CreateTopicDTO $dto): Topic;

    /**
     * Update an existing topic.
     *
     * @param Topic          $topic
     * @param UpdateTopicDTO $dto
     *
     * @return Topic
     */
    public function updateTopic(Topic $topic, UpdateTopicDTO $dto): Topic;

    /**
     * Delete a topic.
     *
     * @param Topic $topic
     *
     * @return bool
     */
    public function deleteTopic(Topic $topic): bool;

    /**
     * Get all topics for a specific course.
     *
     * @param int $courseId
     *
     * @return Collection
     */
    public function getTopicsByCourse(int $courseId): Collection;

    /**
     * Set "is_active" flag to TRUE.
     *
     * @param Topic $topic
     *
     * @return Topic
     */
    public function publish(Topic $topic): Topic;

    /**
     * Set "is_active" flag to FALSE.
     *
     * @param Topic $topic
     *
     * @return Topic
     */
    public function unpublish(Topic $topic): Topic;
}
