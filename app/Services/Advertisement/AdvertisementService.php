<?php

declare(strict_types=1);

namespace App\Services\Advertisement;

use App\DTO\Advertisement\CreateAdvertisementDTO;
use App\DTO\Advertisement\UpdateAdvertisementDTO;
use App\Models\Additional\Advertisement;
use App\Services\Contracts\Advertisement\AdvertisementServiceInterface;
use App\Services\Contracts\Storage\ImageStorageInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

/**
 * Service for handling advertisement-related business logic.
 */
class AdvertisementService implements AdvertisementServiceInterface
{
    /**
     * Constructs a new AdvertisementService instance.
     */
    public function __construct(protected readonly ImageStorageInterface $imageStorage)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAdvertisements(): Collection
    {
        return Advertisement::all();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveAdvertisements(): Collection
    {
        $now = now();

        return Advertisement::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')
                      ->whereNull('ends_at');
                })
                ->orWhere(function ($q) use ($now) {
                    $q->where(function ($q1) use ($now) {
                        $q1->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q2) use ($now) {
                        $q2->whereNull('ends_at')
                           ->orWhere('ends_at', '>=', $now);
                    });
                });
            })
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdvertisementById(int $id): Advertisement
    {
        return Advertisement::findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createAdvertisement(CreateAdvertisementDTO $dto): Advertisement
    {
        $data = $dto->toArray();

        if ($dto->image instanceof UploadedFile) {
            $imagePath = $this->imageStorage->upload($dto->image, 'advertisements');
        }

        $data['image_url'] = $imagePath ?? null;
        unset($data['image']);

        return Advertisement::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAdvertisement(Advertisement $advertisement, UpdateAdvertisementDTO $dto): Advertisement
    {
        $data = $dto->toArray();
        unset($data['id']);

        if (isset($data['image']) && $advertisement->image_url) {
            $this->imageStorage->delete($advertisement->image_url);
        }

        if ($dto->image instanceof UploadedFile) {
            $path = $this->imageStorage->upload($dto->image, 'advertisements');
            $data['image_url'] = $path ?? $advertisement->image_url;
        }

        $advertisement->update($data);
        $advertisement->refresh();

        return $advertisement;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAdvertisement(Advertisement $advertisement): bool
    {
        if ($advertisement->image_url) {
            $this->imageStorage->delete($advertisement->image_url);
        }

        return (bool) $advertisement->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Advertisement $advertisement): Advertisement
    {
        $advertisement->update(['is_active' => true]);
        $advertisement->save();

        return $advertisement->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(Advertisement $advertisement): Advertisement
    {
        $advertisement->update(['is_active' => false]);
        $advertisement->save();

        return $advertisement->refresh();
    }
}
