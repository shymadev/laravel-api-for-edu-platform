<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class BaseStorage
{
    use Traits\UniqueFileNameTrait;

    private const DISK = 'minio';

    /**
     * Upload a file to MinIO storage under the given folder.
     *
     * @param string $folderName
     * @param UploadedFile $fileToUpload
     *
     * @return string|false Public URL on success, false on failure
     */
    public function upload(UploadedFile $fileToUpload, string $folderName): string|false
    {
        try {
            if (!$this->isValidFile($fileToUpload)) {
                return false;
            }

            $filename = $folderName . '/' . $this->generateUniqueFilename($fileToUpload);

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

            $disk = Storage::disk(self::DISK);

            try {
                $written = $disk->writeStream($filename, $stream, ['visibility' => 'public']);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if ($written === false || !$disk->exists($filename)) {
                return false;
            }

            return $disk->url($filename);

        } catch (\Exception $e) {
            Log::error('Failed to upload file to storage', [
                'folder' => $folderName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a file from storage by its URL.
     *
     * @param string $fileUrl
     */
    public function delete(string $fileUrl): bool
    {
        if (str_starts_with($fileUrl, '/storage')) {
            $path = substr($fileUrl, 9);

            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }

            return false;
        }

        $path = $this->urlToPath($fileUrl);

        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->delete($path);
        }

        return false;
    }

    /**
     * Check whether a file exists in storage by its URL.
     *
     * Handles both public-disk files (URLs starting with /storage) and
     * MinIO-hosted files.
     *
     * @param string $fileUrl
     */
    public function exists(string $fileUrl): bool
    {
        if (str_starts_with($fileUrl, '/storage')) {
            $path = substr($fileUrl, 9);

            return Storage::disk('public')->exists($path);
        }

        return Storage::disk(self::DISK)->exists($this->urlToPath($fileUrl));
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
    abstract protected function getStoragePathPrefix(): string|false;

    /**
     * Strip the MinIO public base URL + bucket prefix from a full URL,
     * returning the bare object key (path within the bucket).
     *
     * @param string $url
     *
     * @return string Object key (path within the bucket)
     */
    private function urlToPath(string $url): string
    {
        $bucket = config('filesystems.disks.minio.bucket', 'tallksy');
        // Matches any scheme+host combination followed by /<bucket>/
        $pattern = '#^https?://[^/]+/' . preg_quote($bucket, '#') . '/#';
        $path = preg_replace($pattern, '', $url);

        return $path ?? $url;
    }
}
