<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

abstract class BaseParagraph implements \JsonSerializable
{
    public int $order;
    public string $type;

    abstract public function toArray(): array;

    abstract public static function fromArray(array $data): static;

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
