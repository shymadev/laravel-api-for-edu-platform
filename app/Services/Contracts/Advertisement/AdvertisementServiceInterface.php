<?php

declare(strict_types=1);

namespace App\Services\Contracts\Advertisement;

use App\DTO\Advertisement\CreateAdvertisementDTO;
use App\DTO\Advertisement\UpdateAdvertisementDTO;
use App\Models\Additional\Advertisement;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for advertisement service operations.
 */
interface AdvertisementServiceInterface
{
    /**
     * Get all advertisements.
     *
     * @return Collection
     */
    public function getAllAdvertisements(): Collection;

    /**
     * Get all active advertisements (considering time periods).
     *
     * @return Collection
     */
    public function getActiveAdvertisements(): Collection;

    /**
     * Get an advertisement by its ID.
     *
     * @param int $id
     *
     * @return Advertisement
     */
    public function getAdvertisementById(int $id): Advertisement;

    /**
     * Create a new advertisement.
     *
     * @param CreateAdvertisementDTO $dto
     *
     * @return Advertisement
     */
    public function createAdvertisement(CreateAdvertisementDTO $dto): Advertisement;

    /**
     * Update an existing advertisement.
     *
     * @param Advertisement          $advertisement
     * @param UpdateAdvertisementDTO $dto
     *
     * @return Advertisement
     */
    public function updateAdvertisement(Advertisement $advertisement, UpdateAdvertisementDTO $dto): Advertisement;

    /**
     * Delete an advertisement.
     *
     * @param Advertisement $advertisement
     *
     * @return bool
     */
    public function deleteAdvertisement(Advertisement $advertisement): bool;

    /**
     * Publish an advertisement (set is_active to true).
     *
     * @param Advertisement $advertisement
     *
     * @return Advertisement
     */
    public function publish(Advertisement $advertisement): Advertisement;

    /**
     * Unpublish an advertisement (set is_active to false).
     *
     * @param Advertisement $advertisement
     *
     * @return Advertisement
     */
    public function unpublish(Advertisement $advertisement): Advertisement;
}
