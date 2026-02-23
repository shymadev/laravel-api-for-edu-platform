<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Services\Contracts\Storage\ImageStorageInterface;
use Illuminate\Http\UploadedFile;

class ImageStorageService extends BaseStorage implements ImageStorageInterface
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
