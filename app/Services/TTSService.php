<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\TTSServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TTSService implements TTSServiceInterface
{
    /** Default TTS model. */
    protected const DEFAULT_MODEL = "tts_models/en/ljspeech/tacotron2-DDC";

    protected string $serviceUrl;

    protected int $timeout;

    /**
     * Constructs the TTSService.
     */
    public function __construct()
    {
        $this->serviceUrl = config('services.tts.url', 'http://tts:8000');
        $this->timeout = config('services.tts.timeout', 30);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAudio(string $text, ?string $modelName = null): UploadedFile
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->serviceUrl}/synthesize", [
                    'text' => $this->preprocessGenerationText($text),
                    'model_name' => $modelName ?? self::DEFAULT_MODEL,
                ]);

            if (! $response->successful()) {
                throw new \Exception("TTS service error: " . $response->body());
            }

            $audioBinary = $response->body();
            $tempFilePath = tempnam(sys_get_temp_dir(), 'tts_');
            file_put_contents($tempFilePath, $audioBinary);

            $mime = $response->header('content-type') ?? '';
            if ($mime === '' || ! str_starts_with((string) $mime, 'audio/')) {
                $mime = 'audio/wav';
            }

            return new UploadedFile(
                $tempFilePath,
                Str::slug(substr($text, 0, 20)) . '.wav',
                $mime,
                null,
                true
            );
        } catch (\Exception $e) {
            throw new \Exception("Failed to generate audio: " . $e->getMessage());
        }
    }

    /**
     * Check if the TTS service is healthy.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/health");

            return $response->successful() && $response->json('status') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Preprocess the text before sending it to the TTS service.
     *
     * @param string $text
     *
     * @return string
     */
    protected function preprocessGenerationText(string $text): string
    {
        $preprocessedText = $text;

        if (! str_ends_with($text, '.') && ! str_ends_with($text, '!') && ! str_ends_with($text, '?')) {
            $preprocessedText .= '.';
        }

        return $preprocessedText;
    }
}
