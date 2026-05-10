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

    /**
     * @param int $order
     * @param string $text
     * @param array $gaps
     *
     * @return void
     */
    public function __construct(int $order, string $text, array $gaps)
    {
        $this->order = $order;
        $this->type = 'fill-gaps';
        $this->text = $text;
        $this->gaps = $gaps;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'text' => $this->text,
            'gaps' => $this->gaps,
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
            $data['order'],
            $data['text'] ?? '',
            $data['gaps'] ?? [],
        );
    }
}
