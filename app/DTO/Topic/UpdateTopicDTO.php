<?php

declare(strict_types=1);

namespace App\DTO\Topic;

/**
 * Data Transfer Object for updating a topic.
 */
readonly class UpdateTopicDTO
{
    /**
     * Constructs a new UpdateTopicDTO instance.
     *
     * @param int $id
     * @param int|null $courseId
     * @param string|null $title
     * @param int|null $parentId
     */
    public function __construct(
        public int $id,
        public ?int $courseId = null,
        public ?string $title = null,
        public ?int $parentId = null,
    ) {
    }

    /**
     * Creates an UpdateTopicDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            courseId: isset($data['course_id']) ? (int) $data['course_id'] : null,
            title: $data['title'] ?? null,
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
        );
    }

    /**
     * Converts the DTO to an associative array (excluding null values).
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = ['id' => $this->id];

        if ($this->courseId !== null) {
            $data['course_id'] = $this->courseId;
        }

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->parentId !== null) {
            $data['parent_id'] = $this->parentId;
        }

        return $data;
    }
}
