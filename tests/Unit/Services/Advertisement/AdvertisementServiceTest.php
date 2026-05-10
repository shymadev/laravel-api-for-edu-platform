<?php

declare(strict_types=1);

use App\DTO\Advertisement\CreateAdvertisementDTO;
use App\DTO\Advertisement\UpdateAdvertisementDTO;
use App\Models\Additional\Advertisement;
use App\Services\Advertisement\AdvertisementService;
use App\Services\Storage\ImageStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;

/**
 * Unit tests for AdvertisementService.
 */
uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->imageStorage = Mockery::mock(ImageStorageService::class);
    $this->service = new AdvertisementService($this->imageStorage);
});

/**
 * getAllAdvertisements returns every row regardless of is_active.
 */
it('test_get_all_advertisements', function (int $active, int $inactive): void {
    for ($i = 0; $i < $active; $i++) {
        Advertisement::create(['url' => "https://a{$i}.com", 'image_url' => "https://img.com/{$i}.jpg", 'is_active' => true]);
    }
    for ($i = 0; $i < $inactive; $i++) {
        Advertisement::create(['url' => "https://b{$i}.com", 'image_url' => "https://img.com/i{$i}.jpg", 'is_active' => false]);
    }

    expect($this->service->getAllAdvertisements())->toHaveCount($active + $inactive);
})->with(dataProviderForTestGetAllAdvertisements());

/**
 * Provides counts of active and inactive advertisements for testForGetAllAdvertisements.
 */
function dataProviderForTestGetAllAdvertisements(): array
{
    return [
        'two active one inactive' => [2, 1],
        'none active three inactive' => [0, 3],
    ];
}

/**
 * getActiveAdvertisements returns only rows with is_active true.
 */
it('test_get_active_advertisements', function (int $active, int $inactive): void {
    for ($i = 0; $i < $active; $i++) {
        Advertisement::create(['url' => "https://a{$i}.com", 'image_url' => "https://img.com/{$i}.jpg", 'is_active' => true]);
    }
    for ($i = 0; $i < $inactive; $i++) {
        Advertisement::create(['url' => "https://b{$i}.com", 'image_url' => "https://img.com/i{$i}.jpg", 'is_active' => false]);
    }

    expect($this->service->getActiveAdvertisements())->toHaveCount($active);
})->with(dataProviderForTestGetActiveAdvertisements());

/**
 * Provides counts of active and inactive advertisements for testForGetActiveAdvertisements.
 */
function dataProviderForTestGetActiveAdvertisements(): array
{
    return [
        'one active one inactive' => [1, 1],
        'two active zero inactive' => [2, 0],
    ];
}

/**
 * getAdvertisementById returns the row or throws when the id is unknown.
 */
it('test_get_advertisement_by_id', function (bool $exists): void {
    if ($exists) {
        $ad = Advertisement::create(['url' => 'https://example.com', 'image_url' => 'https://img.com/a.jpg', 'is_active' => true]);
        expect($this->service->getAdvertisementById($ad->id)->id)->toBe($ad->id);
    } else {
        expect(fn () => $this->service->getAdvertisementById(PHP_INT_MAX))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }
})->with(dataProviderForTestGetAdvertisementById());

/**
 * Provides boolean flags for existing and non-existing advertisement IDs for testForGetAdvertisementById.
 */
function dataProviderForTestGetAdvertisementById(): array
{
    return [
        'existing advertisement' => [true],
        'unknown id throws' => [false],
    ];
}

/**
 * createAdvertisement uploads the image and stores the returned URL on the row.
 */
it('test_create_advertisement', function (): void {
    $file = UploadedFile::fake()->create('banner.jpg', 100, 'image/jpeg');

    $this->imageStorage
        ->shouldReceive('upload')
        ->once()
        ->with($file, 'advertisements')
        ->andReturn('https://storage.example.com/advertisements/banner.jpg');

    $dto = new CreateAdvertisementDTO(image: $file, url: 'https://example.com', isActive: true);
    $ad = $this->service->createAdvertisement($dto);

    expect($ad->image_url)->toBe('https://storage.example.com/advertisements/banner.jpg')
        ->and($ad->url)->toBe('https://example.com')
        ->and($ad->is_active)->toBeTrue();
});

/**
 * updateAdvertisement replaces the image when given, or updates fields in place.
 */
it('test_update_advertisement', function (bool $withNewImage): void {
    $ad = Advertisement::create([
        'url' => 'https://old.com',
        'image_url' => 'https://storage.example.com/old.jpg',
        'is_active' => false,
    ]);

    if ($withNewImage) {
        $file = UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg');

        $this->imageStorage->shouldReceive('delete')->once()->with($ad->image_url);
        $this->imageStorage->shouldReceive('upload')->once()->with($file, 'advertisements')
            ->andReturn('https://storage.example.com/new.jpg');

        $dto = new UpdateAdvertisementDTO(id: $ad->id, image: $file, url: 'https://new.com', isActive: true);
        $result = $this->service->updateAdvertisement($ad, $dto);

        expect($result->image_url)->toBe('https://storage.example.com/new.jpg');
    } else {
        $dto = new UpdateAdvertisementDTO(id: $ad->id, url: 'https://updated.com', isActive: true);
        $result = $this->service->updateAdvertisement($ad, $dto);

        expect($result->url)->toBe('https://updated.com');
    }
})->with(dataProviderForTestUpdateAdvertisement());

/**
 * Provides boolean flags for updating advertisement with and without a new image for testForUpdateAdvertisement.
 */
function dataProviderForTestUpdateAdvertisement(): array
{
    return [
        'update with new image' => [true],
        'update without new image' => [false],
    ];
}

/**
 * deleteAdvertisement removes the row and deletes the stored image file.
 */
it('test_delete_advertisement', function (): void {
    $ad = Advertisement::create([
        'url' => 'https://example.com',
        'image_url' => 'https://storage.example.com/ad.jpg',
        'is_active' => true,
    ]);

    $this->imageStorage->shouldReceive('delete')->once()->with($ad->image_url)->andReturn(true);

    expect($this->service->deleteAdvertisement($ad))->toBeTrue()
        ->and(Advertisement::find($ad->id))->toBeNull();
});

/**
 * publish sets is_active to true and persists the change.
 */
it('test_publish', function (): void {
    $ad = Advertisement::create(['url' => 'https://example.com', 'image_url' => 'https://img.com/a.jpg', 'is_active' => false]);

    $result = $this->service->publish($ad);

    expect($result->is_active)->toBeTrue()
        ->and(Advertisement::find($ad->id)->is_active)->toBeTrue();
});

/**
 * unpublish sets is_active to false and persists the change.
 */
it('test_unpublish', function (): void {
    $ad = Advertisement::create(['url' => 'https://example.com', 'image_url' => 'https://img.com/a.jpg', 'is_active' => true]);

    $result = $this->service->unpublish($ad);

    expect($result->is_active)->toBeFalse()
        ->and(Advertisement::find($ad->id)->is_active)->toBeFalse();
});
