<?php

declare(strict_types=1);

namespace App\DTO\Topic;

/**
 * Data Transfer Object for creating a new topic.
 */
readonly class CreateTopicDTO
{
    /**
     * Constructs a new CreateTopicDTO instance.
     *
     * @param int $courseId
     * @param string $title
     * @param int|null $parentId
     */
    public function __construct(
        public int $courseId,
        public string $title,
        public ?int $parentId = null,
    ) {
    }

    /**
     * Creates a CreateTopicDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            courseId: (int) $data['course_id'],
            title: $data['title'],
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'course_id' => $this->courseId,
            'title' => $this->title,
            'parent_id' => $this->parentId,
        ];
    }
}
