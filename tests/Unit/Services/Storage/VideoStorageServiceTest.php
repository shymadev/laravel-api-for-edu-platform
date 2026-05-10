<?php

declare(strict_types=1);

/**
 * Unit tests for VideoStorageService.
 */

use App\Services\Storage\VideoStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('minio');
    $this->service = new VideoStorageService();
});

/**
 * upload returns false for files with disallowed mime types.
 */
it('test_upload_rejects_invalid_mime_type', function (string $mime): void {
    $file = UploadedFile::fake()->create('file.bin', 10, $mime);

    $result = $this->service->upload($file, 'videos');

    expect($result)->toBeFalse();
})->with([
    'audio/mpeg',
    'image/jpeg',
    'application/pdf',
]);

/**
 * upload accepts all declared video mime types.
 */
it('test_upload_accepts_valid_video_mime_types', function (string $mime, string $ext): void {
    $file = UploadedFile::fake()->create("video.{$ext}", 100, $mime);

    $result = $this->service->upload($file, 'lessons');

    expect($result)->not->toBeFalse();
})->with([
    ['video/mp4', 'mp4'],
    ['video/webm', 'webm'],
    ['video/ogg', 'ogv'],
]);

/**
 * upload stores the file under the videos/ prefix and returns a URL.
 */
it('test_upload_returns_url_with_videos_prefix', function (): void {
    $file = UploadedFile::fake()->create('lesson.mp4', 100, 'video/mp4');

    $result = $this->service->upload($file, 'lessons');

    expect($result)->toBeString()
        ->and($result)->toContain('videos/lessons/');
});
