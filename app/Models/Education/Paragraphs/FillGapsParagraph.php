<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a fill-in-the-gaps paragraph in a lesson.
 */
class FillGapsParagraph extends BaseParagraph
{
    public string $text;
    public array $gaps;

    public function __construct(int $order, string $text, array $gaps)
    {
        $this->order = $order;
        $this->type = 'fill-gaps';
        $this->text = $text;
        $this->gaps = $gaps;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'text' => $this->text,
            'gaps' => $this->gaps,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['text'] ?? '',
            $data['gaps'] ?? []
        );
    }
}
