<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a vocabulary game paragraph in a lesson.
 */
class VocabularyGameParagraph extends BaseParagraph
{
    public string $gameType;

    public ?array $words;

    public ?array $guessItems;

    public ?array $oddItems;

    public ?array $mistakeItems;

    public ?array $listenItems;

    /**
     * @param int $order
     * @param string $gameType
     * @param array|null $words
     * @param array|null $guessItems
     * @param array|null $oddItems
     * @param array|null $mistakeItems
     * @param array|null $listenItems
     *
     * @return void
     */
    public function __construct(
        int $order,
        string $gameType,
        ?array $words = null,
        ?array $guessItems = null,
        ?array $oddItems = null,
        ?array $mistakeItems = null,
        ?array $listenItems = null,
    ) {
        $this->order = $order;
        $this->type = 'vocabulary-game';
        $this->gameType = $gameType;
        $this->words = $words;
        $this->guessItems = $guessItems;
        $this->oddItems = $oddItems;
        $this->mistakeItems = $mistakeItems;
        $this->listenItems = $listenItems;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'gameType' => $this->gameType,
            'words' => $this->words,
            'guessItems' => $this->guessItems,
            'oddItems' => $this->oddItems,
            'mistakeItems' => $this->mistakeItems,
            'listenItems' => $this->listenItems,
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
            $data['gameType'],
            $data['words'] ?? null,
            $data['guessItems'] ?? null,
            $data['oddItems'] ?? null,
            $data['mistakeItems'] ?? null,
            $data['listenItems'] ?? null,
        );
    }
}
