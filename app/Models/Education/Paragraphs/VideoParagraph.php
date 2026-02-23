<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class VideoParagraph extends BaseParagraph
{
    public string $url;

    public function __construct(int $order, string $url)
    {
        $this->order = $order;
        $this->type = 'video';
        $this->url = $url;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'url' => $this->url,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['url']
        );
    }
}
