<?php

declare(strict_types=1);

namespace App\DTO\Lesson;

/**
 * Data Transfer Object for creating a new lesson.
 */
readonly class CreateLessonDTO
{
    public function __construct(
        public int $topicId,
        public string $title,
        public int $weight,
        public ?array $content,
        public \ArrayIterator $files,
    ) {
    }

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
