<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\UploadedFile;

/**
 * Service for managing audio file storage.
 */
#[Singleton]
class AudioStorageService extends BaseStorage
{
    /**
     * {@inheritDoc}
     */
    protected function isValidFile(UploadedFile $file): bool
    {
        $availableMimeTypes = [
            'audio/mpeg',
            'audio/wav',
            'audio/mp4',
            'audio/x-wav',
            'audio/ogg',
        ];

        $client = $file->getClientMimeType();
        if (in_array($client, $availableMimeTypes, true)) {
            return true;
        }

        return in_array($file->getMimeType(), $availableMimeTypes, true);
    }

    /**
     * {@inheritDoc}
     */
    protected function getStoragePathPrefix(): string|false
    {
        return 'audio';
    }
}
