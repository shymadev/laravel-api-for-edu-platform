<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Single phrase line inside a phrase paragraph DTO.
 */
class PhraseItem
{
    public string $text;

    public string $translation;

    public ?int $phraseId;

    /**
     * @param string $text
     * @param string $translation
     * @param int|null $phraseId
     *
     * @return void
     */
    public function __construct(string $text, string $translation, ?int $phraseId = null)
    {
        $this->text = $text;
        $this->translation = $translation;
        $this->phraseId = $phraseId;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'translation' => $this->translation,
        ];

        if ($this->phraseId !== null) {
            $data['phrase_id'] = $this->phraseId;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new self(
            $data['text'],
            $data['translation'],
            isset($data['phrase_id']) ? (int) $data['phrase_id'] : null,
        );
    }
}
