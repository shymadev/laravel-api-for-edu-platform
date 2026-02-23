<?php

declare(strict_types=1);

namespace App\DTO\Lesson;

/**
 * Data Transfer Object for updating a lesson.
 */
readonly class UpdateLessonDTO
{
    public function __construct(
        public int $id,
        public ?int $topicId,
        public ?string $title,
        public ?int $weight,
        public ?array $content,
        public \ArrayIterator $files,
    ) {
    }

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
