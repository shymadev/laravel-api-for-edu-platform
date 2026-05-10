<?php

declare(strict_types=1);

namespace App\DTO\Lesson;

/**
 * Data Transfer Object for creating a new lesson.
 */
readonly class CreateLessonDTO
{
    /**
     * @param int $topicId
     * @param string $title
     * @param int $weight
     * @param array|null $content
     * @param \ArrayIterator $files
     */
    public function __construct(
        public int $topicId,
        public string $title,
        public int $weight,
        public ?array $content,
        public \ArrayIterator $files,
    ) {
    }

    /**
     * Creates a CreateLessonDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            topicId: (int) $data['topic_id'],
            title: $data['title'],
            weight: (int) $data['weight'],
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
            'topic_id' => $this->topicId,
            'title' => $this->title,
            'weight' => $this->weight,
            'content' => $this->content,
            'files' => $this->files,
        ];
    }
}
