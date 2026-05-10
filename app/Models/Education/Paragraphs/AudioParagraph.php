<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents an audio paragraph in a lesson.
 */
class AudioParagraph extends BaseParagraph
{
    public ?string $url;

    public ?string $text; // For TTS

    /**
     * @param int $order
     * @param string|null $url
     * @param string|null $text
     *
     * @return void
     */
    public function __construct(int $order, ?string $url = null, ?string $text = null)
    {
        $this->order = $order;
        $this->type = 'audio';
        $this->url = $url;
        $this->text = $text;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'url' => $this->url,
            'text' => $this->text,
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
            $data['url'] ?? null,
            $data['text'] ?? null,
        );
    }
}
