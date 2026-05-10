<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a translation paragraph in a lesson.
 */
class TranslationParagraph extends BaseParagraph
{
    public array $pairs;

    /**
     * @param int $order
     * @param array $pairs
     *
     * @return void
     */
    public function __construct(int $order, array $pairs)
    {
        $this->order = $order;
        $this->type = 'translation';
        $this->pairs = $pairs;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'pairs' => $this->pairs,
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
            $data['pairs'] ?? [],
        );
    }
}
