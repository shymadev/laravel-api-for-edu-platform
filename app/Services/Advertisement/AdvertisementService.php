<?php

declare(strict_types=1);

namespace App\Services\Advertisement;

use App\DTO\Advertisement\CreateAdvertisementDTO;
use App\DTO\Advertisement\UpdateAdvertisementDTO;
use App\Models\Additional\Advertisement;
use App\Services\Storage\ImageStorageService;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

/**
 * Service for handling advertisement-related business logic.
 */
#[Singleton]
class AdvertisementService
{
    /**
     * Constructs a new AdvertisementService instance.
     *
     * @param ImageStorageService $imageStorage
     */
    public function __construct(protected readonly ImageStorageService $imageStorage)
    {
    }

    /**
     * @return Collection<int, Advertisement>
     */
    public function getAllAdvertisements(): Collection
    {
        return Advertisement::all();
    }

    /**
     * @return Collection<int, Advertisement>
     */
    public function getActiveAdvertisements(): Collection
    {
        return Cache::tags(['advertisements'])->remember(
            'advertisements.active',
            300,
            function (): Collection {
                $now = now();

                return Advertisement::where('is_active', true)
                    ->where(function ($query) use ($now): void {
                        $query->where(function ($q): void {
                            $q->whereNull('starts_at')
                                ->whereNull('ends_at');
                        })
                            ->orWhere(function ($q) use ($now): void {
                                $q->where(function ($q1) use ($now): void {
                                    $q1->whereNull('starts_at')
                                        ->orWhere('starts_at', '<=', $now);
                                })
                                    ->where(function ($q2) use ($now): void {
                                        $q2->whereNull('ends_at')
                                            ->orWhere('ends_at', '>=', $now);
                                    });
                            });
                    })
                    ->get();
            },
        );
    }

    /**
     * @param int $id
     *
     * @return Advertisement
     */
    public function getAdvertisementById(int $id): Advertisement
    {
        return Advertisement::findOrFail($id);
    }

    /**
     * @param CreateAdvertisementDTO $dto
     *
     * @return Advertisement
     */
    public function createAdvertisement(CreateAdvertisementDTO $dto): Advertisement
    {
        $data = $dto->toArray();

        $imagePath = $this->imageStorage->upload($dto->image, 'advertisements');

        $data['image_url'] = $imagePath;
        unset($data['image']);

        return Advertisement::create($data);
    }

    /**
     * @param Advertisement $advertisement
     * @param UpdateAdvertisementDTO $dto
     *
     * @return Advertisement
     */
    public function updateAdvertisement(Advertisement $advertisement, UpdateAdvertisementDTO $dto): Advertisement
    {
        $data = $dto->toArray();
        unset($data['id']);

        if (isset($data['image']) && isset($advertisement->image_url)) {
            $this->imageStorage->delete($advertisement->image_url);
        }

        if ($dto->image instanceof UploadedFile) {
            $path = $this->imageStorage->upload($dto->image, 'advertisements');
            $data['image_url'] = $path;
        }

        $advertisement->update($data);
        $advertisement->refresh();

        return $advertisement;
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return boolean
     */
    public function deleteAdvertisement(Advertisement $advertisement): bool
    {
        if (isset($advertisement->image_url)) {
            $this->imageStorage->delete($advertisement->image_url);
        }

        return (bool) $advertisement->delete();
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return Advertisement
     */
    public function publish(Advertisement $advertisement): Advertisement
    {
        $advertisement->update(['is_active' => true]);
        $advertisement->save();

        return $advertisement->refresh();
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return Advertisement
     */
    public function unpublish(Advertisement $advertisement): Advertisement
    {
        $advertisement->update(['is_active' => false]);
        $advertisement->save();

        return $advertisement->refresh();
    }
}
