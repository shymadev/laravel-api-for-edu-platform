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

    public function __construct(
        int $order,
        string $gameType,
        ?array $words = null,
        ?array $guessItems = null,
        ?array $oddItems = null,
        ?array $mistakeItems = null,
        ?array $listenItems = null
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

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['gameType'],
            $data['words'] ?? null,
            $data['guessItems'] ?? null,
            $data['oddItems'] ?? null,
            $data['mistakeItems'] ?? null,
            $data['listenItems'] ?? null
        );
    }
}
