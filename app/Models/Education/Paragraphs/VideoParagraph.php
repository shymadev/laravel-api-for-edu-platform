<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for an embedded video paragraph.
 */
class VideoParagraph extends BaseParagraph
{
    public string $url;

    /**
     * @param int $order
     * @param string $url
     *
     * @return void
     */
    public function __construct(int $order, string $url)
    {
        $this->order = $order;
        $this->type = 'video';
        $this->url = $url;
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
            $data['url'],
        );
    }
}
