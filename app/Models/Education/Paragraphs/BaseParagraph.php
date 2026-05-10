<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Base JSON-serializable block for lesson content (Editor.js / API payload).
 */
abstract class BaseParagraph implements \JsonSerializable
{
    public int $order;

    public string $type;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    abstract public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
