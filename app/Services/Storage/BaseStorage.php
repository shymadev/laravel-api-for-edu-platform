<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Services\Contracts\Storage\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class BaseStorage implements FileStorageInterface
{
    use Traits\UniqueFileNameTrait;

    /**
     * {@inheritdoc}
     */
    public function upload(UploadedFile $fileToUpload, string $folderName): string | false
    {
        try {
            if (! $this->isValidFile($fileToUpload)) {
                return false;
            }

            $filename = $folderName . '/' .$this->generateUniqueFilename($fileToUpload);

            if ($this->getStoragePathPrefix()) {
                $filename = $this->getStoragePathPrefix() . '/' . $filename;
            }

            $realPath = $fileToUpload->getRealPath();
            if ($realPath === false) {
                return false;
            }

            $stream = fopen($realPath, 'rb');
            if ($stream === false) {
                return false;
            }

            try {
                Storage::disk('public')->writeStream($filename, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            return '/storage/' . $filename;

        } catch (\Exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $fileUrl): bool
    {
        if (str_starts_with($fileUrl, '/storage')) {
            $fileUrl = substr($fileUrl, 9);
        }

        if (Storage::disk('public')->exists($fileUrl)) {
            return Storage::disk('public')->delete($fileUrl);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $fileUrl): bool
    {
        if (str_starts_with($fileUrl, '/storage')) {
            $fileUrl = substr($fileUrl, 9);
        }

        return Storage::disk('public')->exists($fileUrl);
    }

    /**
     * Validate the uploaded file based on specific criteria.
     *
     * @param UploadedFile $file
     *
     * @return bool True if the file is valid, false otherwise
     */
    abstract protected function isValidFile(UploadedFile $file): bool;

    /**
     * Get the storage path prefix for the specific file type.
     *
     * @return string|false The storage path prefix or false if not applicable
     */
    abstract protected function getStoragePathPrefix(): string | false;
}
