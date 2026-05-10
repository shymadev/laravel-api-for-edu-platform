<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a matching exercise paragraph in a lesson.
 */
class MatchingParagraph extends BaseParagraph
{
    public array $pairs;

    public bool $shuffleRight;

    /**
     * @param int $order
     * @param array $pairs
     * @param bool $shuffleRight
     *
     * @return void
     */
    public function __construct(int $order, array $pairs, bool $shuffleRight = true)
    {
        $this->order = $order;
        $this->type = 'matching';
        $this->pairs = $pairs;
        $this->shuffleRight = $shuffleRight;
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
            'shuffleRight' => $this->shuffleRight,
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
            $data['shuffleRight'] ?? true,
        );
    }
}
