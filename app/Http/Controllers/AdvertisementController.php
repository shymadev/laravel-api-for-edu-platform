<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Advertisement\CreateAdvertisementRequest;
use App\Http\Requests\Advertisement\UpdateAdvertisementRequest;
use App\Http\Resources\Advertisement\AdvertisementResource;
use App\Models\Additional\Advertisement;
use App\Services\Contracts\Advertisement\AdvertisementServiceInterface;
use App\Services\Storage\ImageStorageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class AdvertisementController extends Controller
{
    public function __construct(
        protected readonly AdvertisementServiceInterface $advertisementService,
        protected readonly ImageStorageService $imageStorage,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return AdvertisementResource::collection(
            $this->advertisementService->getAllAdvertisements()
        );
    }

    public function active(): AnonymousResourceCollection
    {
        return AdvertisementResource::collection(
            $this->advertisementService->getActiveAdvertisements()
        );
    }

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

    public function show(Advertisement $advertisement): JsonResponse
    {
        return response()->json(
            AdvertisementResource::make($advertisement)
        );
    }

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
