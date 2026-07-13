<?php

declare(strict_types=1);

namespace App\DTO\Advertisement;

use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object for updating an advertisement.
 */
readonly class UpdateAdvertisementDTO
{
    /**
     * Constructs a new UpdateAdvertisementDTO instance.
     *
     * @param int $id
     * @param UploadedFile|null $image
     * @param string|null $url
     * @param bool|null $isActive
     * @param string|null $startsAt
     * @param string|null $endsAt
     * @param ?bool $isPermanent
     */
    public function __construct(
        public int $id,
        public ?UploadedFile $image = null,
        public ?string $url = null,
        public ?bool $isActive = null,
        public ?string $startsAt = null,
        public ?string $endsAt = null,
        public ?bool $isPermanent = null,
    ) {
    }

    /**
     * Creates an UpdateAdvertisementDTO from an associative array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $isActive = null;

        if (isset($data['is_active'])) {
            $value = $data['is_active'];
            if (is_bool($value)) {
                $isActive = $value;
            } elseif (is_string($value)) {
                $isActive = in_array(strtolower($value), ['true', '1'], true);
            } elseif (is_int($value)) {
                $isActive = $value === 1;
            }
        }

        $isPermanent = null;
        if (isset($data['is_permanent'])) {
            $value = $data['is_permanent'];
            if (is_bool($value)) {
                $isPermanent = $value;
            } elseif (is_string($value)) {
                $isPermanent = in_array(strtolower($value), ['true', '1'], true);
            } elseif (is_int($value)) {
                $isPermanent = $value === 1;
            }
        }

        return new self(
            id: (int) $data['id'],
            image: $data['image'] ?? null,
            url: $data['url'] ?? null,
            isActive: $isActive,
            startsAt: $data['starts_at'] ?? null,
            endsAt: $data['ends_at'] ?? null,
            isPermanent: $isPermanent,
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = array_filter([
            'image' => $this->image,
            'url' => $this->url,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ], fn ($value) => $value !== null);

        if ($this->isPermanent === true) {
            $data['starts_at'] = null;
            $data['ends_at'] = null;
        }

        return $data;
    }
}
