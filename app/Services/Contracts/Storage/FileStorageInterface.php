<?php

declare(strict_types=1);

namespace App\Services\Contracts\Storage;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    /**
     * Upload a file to storage.
     *
     * @param UploadedFile $fileToUpload
     * @param string       $folderName
     *
     * @return string|false The URL of the uploaded file, or false on failure
     */
    public function upload(UploadedFile $fileToUpload, string $folderName): string | false;

    /**
     * Delete a file from storage.
     *
     * @param string $fileUrl
     *
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(string $fileUrl): bool;

    /**
     * Check if a file exists in storage.
     *
     * @param string $fileUrl
     *
     * @return bool True if the file exists, false otherwise
     */
    public function exists(string $fileUrl): bool;
}
