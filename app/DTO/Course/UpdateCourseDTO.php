<?php

declare(strict_types=1);

namespace App\DTO\Course;

/**
 * Data Transfer Object for updating a course.
 */
readonly class UpdateCourseDTO
{
    /**
     * Constructs a new UpdateCourseDTO instance.
     *
     * @param int $id
     * @param string|null $title
     * @param string|null $language
     * @param string|null $description
     * @param int|null $difficultyLevelId
     * @param string|null $previewImage
     * @param bool|null $isPremium
     */
    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $language = null,
        public ?string $description = null,
        public ?int $difficultyLevelId = null,
        public ?string $previewImage = null,
        public ?bool $isPremium = null,
    ) {
    }

    /**
     * Creates an UpdateCourseDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'] ?? null,
            language: $data['language'] ?? null,
            description: $data['description'] ?? null,
            difficultyLevelId: isset($data['difficulty_level_id']) ? (int) $data['difficulty_level_id'] : null,
            previewImage: $data['preview_image'] ?? null,
            isPremium: isset($data['is_premium']) ? (bool) $data['is_premium'] : null,
        );
    }

    /**
     * Converts the DTO to an associative array (excluding null values).
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
        ];

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->language !== null) {
            $data['language'] = $this->language;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->difficultyLevelId !== null) {
            $data['difficulty_level_id'] = $this->difficultyLevelId;
        }

        if ($this->previewImage !== null) {
            $data['preview_image'] = $this->previewImage;
        }

        if ($this->isPremium !== null) {
            $data['is_premium'] = $this->isPremium;
        }

        return $data;
    }
}
