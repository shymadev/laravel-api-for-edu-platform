<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for one answer option in a test question.
 */
class TestOption extends BaseParagraph
{
    public string $text;

    public bool $isCorrect;

    /**
     * @param string $text
     * @param bool $isCorrect
     *
     * @return void
     */
    public function __construct(string $text, bool $isCorrect)
    {
        $this->text = $text;
        $this->isCorrect = $isCorrect;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'is_correct' => $this->isCorrect,
        ];
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
            $data['is_correct'] ?? false,
        );
    }
}
