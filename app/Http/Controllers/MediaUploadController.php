<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Media\UploadVideoRequest;
use App\Services\Storage\VideoStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Handles HTTP requests for media file uploads.
 */
class MediaUploadController extends Controller
{
    /**
     * @param \App\Services\Storage\VideoStorageService $videoStorage
     */
    public function __construct(
        private readonly VideoStorageService $videoStorage,
    ) {
    }

    /**
     * Upload a video file to object storage and return the public URL.
     *
     * @param \App\Http\Requests\Media\UploadVideoRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVideo(UploadVideoRequest $request): JsonResponse
    {
        $file = $request->file('video');

        $url = $this->videoStorage->upload($file, 'lessons');

        if ($url === false) {
            Log::error('Video upload failed', ['user_id' => auth()->id()]);

            return response()->json(['message' => 'Не удалось загрузить видео.'], 500);
        }

        return response()->json(['url' => $url], 201);
    }
}
