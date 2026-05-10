<?php

declare(strict_types=1);

namespace App\DTO\Course;

/**
 * Data Transfer Object for creating a new course.
 */
readonly class CreateCourseDTO
{
    /**
     * Constructs a new CreateCourseDTO instance.
     *
     * @param string $title
     * @param string $language
     * @param string|null $description
     * @param int|null $difficultyLevelId
     * @param string|null $previewImage
     * @param bool $isPremium
     */
    public function __construct(
        public string $title,
        public string $language,
        public ?string $description = null,
        public ?int $difficultyLevelId = null,
        public ?string $previewImage = null,
        public bool $isPremium = false,
    ) {
    }

    /**
     * Creates a CreateCourseDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $isPremium = $data['is_premium'];
        $isPremiumConverted = false;

        if (is_bool($isPremium)) {
            $isPremiumConverted = $isPremium;
        }

        if (is_string($isPremium)) {
            $isPremiumConverted = in_array(strtolower($isPremium), ['true', '1'], true);
        }

        if (is_int($isPremium)) {
            $isPremiumConverted = $isPremium === 1;
        }

        return new self(
            title: $data['title'],
            language: $data['language'],
            description: $data['description'] ?? null,
            difficultyLevelId: isset($data['difficulty_level_id']) ? (int) $data['difficulty_level_id'] : null,
            previewImage: $data['preview_image'] ?? null,
            isPremium: $isPremiumConverted,
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
            'title' => $this->title,
            'language' => $this->language,
            'description' => $this->description,
            'difficulty_level_id' => $this->difficultyLevelId,
            'preview_image' => $this->previewImage,
            'is_premium' => $this->isPremium,
        ];
    }

    /**
     * Normalizes request payload values for `is_premium` to a boolean.
     *
     * @param mixed $value
     *
     * @return boolean
     */
    protected function convertIsPremiumToBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1'], true);
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return false;
    }
}
