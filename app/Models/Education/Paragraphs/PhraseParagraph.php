<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class PhraseParagraph extends BaseParagraph
{
    /** @var PhraseItem[] */
    public array $phrases;

    /**
     * @param int          $order
     * @param PhraseItem[] $phrases
     */
    public function __construct(int $order, array $phrases)
    {
        $this->order = $order;
        $this->type = 'phrases';
        $this->phrases = $phrases;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'phrases' => array_map(fn (PhraseItem $item) => $item->toArray(), $this->phrases),
        ];
    }

    public static function fromArray(array $data): static
    {
        $phrases = array_map(
            fn (array $itemData) => PhraseItem::fromArray($itemData),
            $data['phrases'] ?? []
        );

        return new static(
            $data['order'],
            $phrases
        );
    }
}
