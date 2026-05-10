<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Advertisement\CreateAdvertisementRequest;
use App\Http\Requests\Advertisement\UpdateAdvertisementRequest;
use App\Http\Resources\Advertisement\AdvertisementResource;
use App\Models\Additional\Advertisement;
use App\Services\Advertisement\AdvertisementService;
use App\Services\Storage\ImageStorageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Advertisement controller.
 */
class AdvertisementController extends Controller
{
    /**
     * Constructs a new AdvertisementController instance.
     *
     * @param \App\Services\Advertisement\AdvertisementService $advertisementService
     * @param \App\Services\Storage\ImageStorageService $imageStorage
     *
     * @return void
     */
    public function __construct(
        protected readonly AdvertisementService $advertisementService,
        protected readonly ImageStorageService $imageStorage,
    ) {
    }

    /**
     * Get all advertisements.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return AdvertisementResource::collection(
            $this->advertisementService->getAllAdvertisements(),
        );
    }

    /**
     * Get all active advertisements.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function active(): AnonymousResourceCollection
    {
        return AdvertisementResource::collection(
            $this->advertisementService->getActiveAdvertisements(),
        );
    }

    /**
     * Create a new advertisement.
     *
     * @param \App\Http\Requests\Advertisement\CreateAdvertisementRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateAdvertisementRequest $request): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService
                ->createAdvertisement($request->toDTO());

            Log::channel('db')->info('Advertisement created', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'create',
            ]);

            return AdvertisementResource::make($advertisement)
                ->response()
                ->setStatusCode(201);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to create advertisement', [
                'user_id' => auth()->id(),
                'action' => 'create',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to create advertisement'], 500);
        }
    }

    /**
     * Get an advertisement by its ID.
     *
     * @param \App\Models\Additional\Advertisement $advertisement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Advertisement $advertisement): JsonResponse
    {
        return response()->json(
            AdvertisementResource::make($advertisement),
        );
    }

    /**
     * Update an advertisement.
     *
     * @param \App\Http\Requests\Advertisement\UpdateAdvertisementRequest $request
     * @param \App\Models\Additional\Advertisement $advertisement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService
                ->updateAdvertisement($advertisement, $request->toDTO());

            Log::channel('db')->info('Advertisement updated', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'update',
            ]);

            return AdvertisementResource::make($advertisement)->response();

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Advertisement not found'], 404);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to update advertisement', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'update',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to update advertisement'], 500);
        }
    }

    /**
     * Delete an advertisement.
     *
     * @param \App\Models\Additional\Advertisement $advertisement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Advertisement $advertisement): JsonResponse
    {
        try {
            $this->advertisementService->deleteAdvertisement($advertisement);

            Log::channel('db')->info('Advertisement deleted', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'delete',
            ]);

            return response()->json(null, 204);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to delete advertisement', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'delete',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to delete advertisement'], 500);
        }
    }

    /**
     * Publish an advertisement.
     *
     * @param \App\Models\Additional\Advertisement $advertisement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Advertisement $advertisement): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->publish($advertisement);

            Log::channel('db')->info('Advertisement published', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'publish',
            ]);

            return AdvertisementResource::make($advertisement)->response();

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to publish advertisement', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'publish',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to publish advertisement'], 500);
        }
    }

    /**
     * Unpublish an advertisement.
     *
     * @param \App\Models\Additional\Advertisement $advertisement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unpublish(Advertisement $advertisement): JsonResponse
    {
        try {
            $advertisement = $this->advertisementService->unpublish($advertisement);

            Log::channel('db')->info('Advertisement unpublished', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'unpublish',
            ]);

            return AdvertisementResource::make($advertisement)->response();

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to unpublish advertisement', [
                'advertisement_id' => $advertisement->id,
                'user_id' => auth()->id(),
                'action' => 'unpublish',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to unpublish advertisement'], 500);
        }
    }
}
