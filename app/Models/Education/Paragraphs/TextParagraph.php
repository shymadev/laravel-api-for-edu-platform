<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for a rich text paragraph.
 */
class TextParagraph extends BaseParagraph
{
    public string $content;

    /**
     * @param int $order
     * @param string $content
     *
     * @return void
     */
    public function __construct(int $order, string $content)
    {
        $this->order = $order;
        $this->type = 'text';
        $this->content = $content;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'content' => $this->content,
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
            $data['content'],
        );
    }
}
