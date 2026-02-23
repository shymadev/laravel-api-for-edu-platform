<?php

declare(strict_types=1);

namespace App\Services\Storage\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait UniqueFileNameTrait
{
    /**
     * Generate a unique filename for the uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }
}
