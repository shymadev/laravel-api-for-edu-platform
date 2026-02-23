<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface TTSServiceInterface
{
    /**
     * Generate audio for the given text.
     *
     * @param string      $text      The text to convert to speech
     * @param string|null $modelName The TTS model to use (optional)
     *
     * @return UploadedFile Generated audio file
     */
    public function generateAudio(
        string $text,
        ?string $modelName = null,
    ): UploadedFile;

    /**
     * Check if the TTS service is healthy and available.
     *
     * @return bool
     */
    public function isHealthy(): bool;
}
