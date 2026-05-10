<?php

declare(strict_types=1);

namespace App\DTO\Lesson;

/**
 * Data Transfer Object for updating a lesson.
 */
readonly class UpdateLessonDTO
{
    /**
     * @param int $id
     * @param int|null $topicId
     * @param string|null $title
     * @param int|null $weight
     * @param array|null $content
     * @param \ArrayIterator $files
     */
    public function __construct(
        public int $id,
        public ?int $topicId,
        public ?string $title,
        public ?int $weight,
        public ?array $content,
        public \ArrayIterator $files,
    ) {
    }

    /**
     * Creates an UpdateLessonDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            topicId: isset($data['topic_id']) ? (int) $data['topic_id'] : null,
            title: $data['title'] ?? null,
            weight: isset($data['weight']) ? (int) $data['weight'] : null,
            content: $data['content'] ?? null,
            files: $data['files'] ?? null,
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'topic_id' => $this->topicId,
            'title' => $this->title,
            'weight' => $this->weight,
            'content' => $this->content,
            'files' => $this->files,
        ];
    }
}
