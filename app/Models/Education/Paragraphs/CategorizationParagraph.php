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

    /**
     * @param int $order
     * @param array $categories
     * @param array $items
     *
     * @return void
     */
    public function __construct(int $order, array $categories, array $items)
    {
        $this->order = $order;
        $this->type = 'categorization';
        $this->categories = $categories;
        $this->items = $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'categories' => $this->categories,
            'items' => $this->items,
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
            $data['categories'] ?? [],
            $data['items'] ?? [],
        );
    }
}
