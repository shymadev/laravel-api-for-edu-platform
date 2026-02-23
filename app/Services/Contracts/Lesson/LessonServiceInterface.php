<?php

declare(strict_types=1);

namespace App\Services\Contracts\Lesson;

use App\DTO\Lesson\CreateLessonDTO;
use App\DTO\Lesson\UpdateLessonDTO;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface LessonServiceInterface
{
    /**
     * Get a lesson by its ID.
     *
     * @param int $id
     *
     * @return Lesson
     */
    public function getLessonById(int $id): Lesson;

    /**
     * Create a new lesson.
     *
     * @param CreateLessonDTO $dto
     *
     * @return Lesson
     */
    public function createLesson(CreateLessonDTO $dto): Lesson;

    /**
     * Update a lesson.
     *
     * @param Lesson          $lesson
     * @param UpdateLessonDTO $dto
     *
     * @return Lesson
     */
    public function updateLesson(Lesson $lesson, UpdateLessonDTO $dto): Lesson;

    /**
     * Delete a lesson.
     *
     * @param Lesson $lesson
     *
     * @return bool
     */
    public function deleteLesson(Lesson $lesson): bool;

    /**
     * Get lessons by topic.
     *
     * @return AnonymousResourceCollection<Lesson>
     */
    public function getLessonsByTopic(Topic $topic): AnonymousResourceCollection;

    /**
     * Validate lesson content structure.
     *
     * @param array|null $content
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validateContent(?array $content): array;

    /**
     * Publish a lesson.
     *
     * @param Lesson $lesson
     *
     * @return Lesson
     */
    public function publish(Lesson $lesson): Lesson;

    /**
     * Unpublish a lesson.
     *
     * @param Lesson $lesson
     *
     * @return Lesson
     */
    public function unpublish(Lesson $lesson): Lesson;
}
