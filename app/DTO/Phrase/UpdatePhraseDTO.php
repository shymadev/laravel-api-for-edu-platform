<?php

declare(strict_types=1);

namespace App\DTO\Phrase;

/**
 * Data Transfer Object for updating a phrase.
 */
readonly class UpdatePhraseDTO
{
    /**
     * Constructs an UpdatePhraseDTO instance.
     *
     * @param string|null $text
     * @param string|null $translation
     * @param int|null    $difficultyLevelId
     * @param string|null $topic
     */
    public function __construct(
        public ?string $text = null,
        public ?string $translation = null,
        public ?int $difficultyLevelId = null,
        public ?string $topic = null,
    ) {
    }

    /**
     * Create an UpdatePhraseDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? null,
            translation: $data['translation'] ?? null,
            difficultyLevelId: isset($data['difficulty_level_id']) ? (int) $data['difficulty_level_id'] : null,
            topic: $data['topic'] ?? null,
        );
    }

    /**
     * Convert the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'translation' => $this->translation,
            'difficulty_level_id' => $this->difficultyLevelId,
            'topic' => $this->topic,
        ];
    }
}
