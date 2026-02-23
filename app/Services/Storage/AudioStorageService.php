<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Services\Contracts\Storage\AudioStorageInterface;
use Illuminate\Http\UploadedFile;

/**
 * Service for managing audio file storage.
 */
class AudioStorageService extends BaseStorage implements AudioStorageInterface
{
    /**
     * {@inheritdoc}
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

        return in_array($file->getMimeType(), $availableMimeTypes, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getStoragePathPrefix(): string|false
    {
        return 'audio';
    }
}
