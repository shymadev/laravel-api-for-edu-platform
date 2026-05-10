<?php

declare(strict_types=1);

/**
 * Unit tests for AudioStorageService.
 */

use App\Services\Storage\AudioStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('minio');
    $this->service = new AudioStorageService();
});

/**
 * upload returns false for files with disallowed mime types.
 */
it('test_upload_rejects_invalid_mime_type', function (string $mime): void {
    $file = UploadedFile::fake()->create('file.txt', 10, $mime);

    $result = $this->service->upload($file, 'audio_test');

    expect($result)->toBeFalse();
})->with([
    'image/jpeg',
    'application/pdf',
    'text/plain',
]);

/**
 * upload accepts all declared audio mime types.
 */
it('test_upload_accepts_valid_audio_mime_types', function (string $mime): void {
    $file = UploadedFile::fake()->create('audio.wav', 10, $mime);

    $result = $this->service->upload($file, 'audio_test');

    expect($result)->not->toBeFalse();
})->with([
    'audio/wav',
    'audio/mpeg',
    'audio/ogg',
]);

/**
 * upload stores the file under the audio/ prefix and returns a URL string.
 */
it('test_upload_returns_url_with_audio_prefix', function (): void {
    $file = UploadedFile::fake()->create('sound.wav', 10, 'audio/wav');

    $result = $this->service->upload($file, 'phrases');

    expect($result)->toBeString()
        ->and($result)->toContain('audio/phrases/');
});

/**
 * delete returns false when the URL does not exist in storage.
 */
it('test_delete_returns_false_for_nonexistent_file', function (): void {
    $result = $this->service->delete('https://cdn.example.com/tallksy/audio/phrases/nonexistent.wav');

    expect($result)->toBeFalse();
});

/**
 * exists returns false for a URL that was never uploaded.
 */
it('test_exists_returns_false_for_unknown_url', function (): void {
    expect($this->service->exists('https://cdn.example.com/tallksy/audio/phrases/missing.wav'))->toBeFalse();
});
