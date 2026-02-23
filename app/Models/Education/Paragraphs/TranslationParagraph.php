<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a translation paragraph in a lesson.
 */
class TranslationParagraph extends BaseParagraph
{
    public array $pairs;

    public function __construct(int $order, array $pairs)
    {
        $this->order = $order;
        $this->type = 'translation';
        $this->pairs = $pairs;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'pairs' => $this->pairs,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['pairs'] ?? []
        );
    }
}
