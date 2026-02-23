<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class PhraseItem
{
    public string $text;
    public string $translation;

    public function __construct(string $text, string $translation)
    {
        $this->text = $text;
        $this->translation = $translation;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'translation' => $this->translation,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['text'],
            $data['translation']
        );
    }
}
