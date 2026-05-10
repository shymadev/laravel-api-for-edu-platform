<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\UploadedFile;

/**
 * Service for managing video file storage.
 */
#[Singleton]
class VideoStorageService extends BaseStorage
{
    protected function isValidFile(UploadedFile $file): bool
    {
        $availableMimeTypes = [
            'video/mp4',
            'video/mpeg',
            'video/webm',
            'video/ogg',
            'video/quicktime',
            'video/x-msvideo',
        ];

        $client = $file->getClientMimeType();
        if (in_array($client, $availableMimeTypes, true)) {
            return true;
        }

        return in_array($file->getMimeType(), $availableMimeTypes, true);
    }

    protected function getStoragePathPrefix(): string|false
    {
        return 'videos';
    }
}
