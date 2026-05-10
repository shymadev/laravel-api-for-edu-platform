<?php

declare(strict_types=1);

namespace App\DTO\Phrase;

/**
 * Data Transfer Object for creating a new phrase.
 */
readonly class CreatePhraseDTO
{
    /**
     * @param string $text
     * @param string|null $translation
     * @param int|null $difficultyLevelId
     * @param string|null $topic
     * @param string|null $audio
     */
    public function __construct(
        public string $text,
        public ?string $translation = null,
        public ?int $difficultyLevelId = null,
        public ?string $topic = null,
        public ?string $audio = null,
    ) {
    }

    /**
     * Creates a CreatePhraseDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
            translation: $data['translation'] ?? null,
            difficultyLevelId: isset($data['difficulty_level_id']) ? (int) $data['difficulty_level_id'] : null,
            topic: $data['topic'] ?? null,
            audio: $data['audio'] ?? null,
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
            'text' => $this->text,
            'translation' => $this->translation,
            'difficulty_level_id' => $this->difficultyLevelId,
            'topic' => $this->topic,
            'audio' => $this->audio,
        ];
    }
}
