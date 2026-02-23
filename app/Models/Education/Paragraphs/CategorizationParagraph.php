<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a categorization paragraph in a lesson.
 */
class CategorizationParagraph extends BaseParagraph
{
    public array $categories;
    public array $items;

    public function __construct(int $order, array $categories, array $items)
    {
        $this->order = $order;
        $this->type = 'categorization';
        $this->categories = $categories;
        $this->items = $items;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'categories' => $this->categories,
            'items' => $this->items,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['categories'] ?? [],
            $data['items'] ?? []
        );
    }
}
