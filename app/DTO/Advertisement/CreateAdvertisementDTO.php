<?php

declare(strict_types=1);

namespace App\DTO\Advertisement;

use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object for creating a new advertisement.
 */
readonly class CreateAdvertisementDTO
{
    /**
     * Constructs a new CreateAdvertisementDTO instance.
     *
     * @param UploadedFile $image
     * @param string       $url
     * @param bool         $isActive
     * @param string|null  $startsAt
     * @param string|null  $endsAt
     */
    public function __construct(
        public UploadedFile $image,
        public string $url,
        public bool $isActive = true,
        public ?string $startsAt = null,
        public ?string $endsAt = null,
    ) {
    }

    /**
     * Creates a CreateAdvertisementDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $isActive = $data['is_active'] ?? true;
        $isActiveConverted = false;

        if (is_bool($isActive)) {
            $isActiveConverted = $isActive;
        }

        if (is_string($isActive)) {
            $isActiveConverted = in_array(strtolower($isActive), ['true', '1'], true);
        }

        if (is_int($isActive)) {
            $isActiveConverted = $isActive === 1;
        }

        return new self(
            image: $data['image'],
            url: $data['url'],
            isActive: $isActiveConverted,
            startsAt: $data['starts_at'] ?? null,
            endsAt: $data['ends_at'] ?? null,
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'image' => $this->image,
            'url' => $this->url,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ];
    }
}
