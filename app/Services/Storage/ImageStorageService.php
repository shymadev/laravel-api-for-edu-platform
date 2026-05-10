<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\UploadedFile;

#[Singleton]
class ImageStorageService extends BaseStorage
{
    protected function isValidFile(UploadedFile $file): bool
    {
        $availableMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
        ];

        return in_array($file->getMimeType(), $availableMimeTypes, true);
    }

    protected function getStoragePathPrefix(): string|false
    {
        return 'images';
    }
}
