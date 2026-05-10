<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Http;

/**
 * Service to transcribe text using Espoke.
 */
#[Singleton]
class EspokeTranscriptionService
{
    /**
     * Transcribe text using Espoke.
     *
     * @param string $text
     *
     * @return string
     */
    public function transcribe(string $text): string
    {
        $url = config('services.espoke.url') . '/transcribe';

        $response = Http::timeout(config('services.espoke.timeout'))->post($url, [
            'text' => $text,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to transcribe text: ' . $response->body());
        }

        $data = $response->json();

        return $data['transcription'];
    }
}
