<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class TestOption extends BaseParagraph
{
    public string $text;
    public bool $isCorrect;

    public function __construct(string $text, bool $isCorrect)
    {
        $this->text = $text;
        $this->isCorrect = $isCorrect;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'is_correct' => $this->isCorrect,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['text'],
            $data['is_correct'] ?? false
        );
    }
}
