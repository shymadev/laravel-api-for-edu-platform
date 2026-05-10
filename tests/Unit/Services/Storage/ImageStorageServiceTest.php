<?php

declare(strict_types=1);

/**
 * Unit tests for ImageStorageService.
 */

use App\Services\Storage\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('minio');
    $this->service = new ImageStorageService();
});

/**
 * upload rejects non-image mime types and returns false.
 */
it('test_upload_rejects_invalid_mime_type', function (string $mime): void {
    $file = UploadedFile::fake()->create('file.bin', 10, $mime);

    $result = $this->service->upload($file, 'avatars');

    expect($result)->toBeFalse();
})->with([
    'audio/wav',
    'application/pdf',
    'text/plain',
    'video/mp4',
]);

/**
 * upload accepts JPEG, PNG, WebP and SVG images.
 */
it('test_upload_accepts_valid_image_mime_types', function (string $mime, string $ext): void {
    $file = UploadedFile::fake()->create("photo.{$ext}", 10, $mime);

    $result = $this->service->upload($file, 'avatars');

    expect($result)->not->toBeFalse();
})->with([
    ['image/jpeg', 'jpg'],
    ['image/png', 'png'],
    ['image/webp', 'webp'],
    ['image/svg+xml', 'svg'],
]);

/**
 * upload stores the file under the images/ prefix and returns a URL.
 */
it('test_upload_returns_url_with_images_prefix', function (): void {
    $file = UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg');

    $result = $this->service->upload($file, 'course_images');

    expect($result)->toBeString()
        ->and($result)->toContain('images/course_images/');
});
