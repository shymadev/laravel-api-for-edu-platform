<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class TextParagraph extends BaseParagraph
{
    public string $content;

    public function __construct(int $order, string $content)
    {
        $this->order = $order;
        $this->type = 'text';
        $this->content = $content;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'content' => $this->content,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['content']
        );
    }
}
