<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for a phrase-matching or phrase list paragraph.
 */
class PhraseParagraph extends BaseParagraph
{
    /**
     * @var PhraseItem[]
     */
    public array $phrases;

    /**
     * @param int $order
     * @param PhraseItem[] $phrases
     *
     * @return void
     */
    public function __construct(int $order, array $phrases)
    {
        $this->order = $order;
        $this->type = 'phrases';
        $this->phrases = $phrases;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'phrases' => array_map(fn (PhraseItem $item) => $item->toArray(), $this->phrases),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $phrases = array_map(
            fn (array $itemData) => PhraseItem::fromArray($itemData),
            $data['phrases'] ?? [],
        );

        return new static(
            $data['order'],
            $phrases,
        );
    }
}
