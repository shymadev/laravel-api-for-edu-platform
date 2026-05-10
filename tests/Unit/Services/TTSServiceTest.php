<?php

declare(strict_types=1);

/**
 * Unit tests for TTSService.
 */

use App\Services\TTSService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->service = new TTSService();
});

/**
 * isHealthy is true only when /health returns JSON status "ok".
 */
it('test_is_healthy', function (string $status, bool $expected): void {
    Http::fake(['*/health' => Http::response(['status' => $status], 200)]);

    expect($this->service->isHealthy())->toBe($expected);
})->with(dataProviderForTestIsHealthy());

/**
 * Provides status strings and expected boolean results for testForIsHealthy.
 */
function dataProviderForTestIsHealthy(): array
{
    return [
        'ok status → true' => ['ok', true],
        'wrong status → false' => ['error', false],
    ];
}

/**
 * isHealthy returns false when the HTTP client throws (e.g. unreachable host).
 */
it('test_is_healthy_on_connection_failure', function (): void {
    Http::fake([
        '*/health' => fn () => throw new \Exception('Connection refused'),
    ]);

    expect($this->service->isHealthy())->toBeFalse();
});

/**
 * generateAudio yields an UploadedFile with a .wav name on HTTP 200.
 */
it('test_generate_audio', function (): void {
    Http::fake(['*/synthesize' => Http::response(str_repeat('x', 100), 200, ['content-type' => 'audio/wav'])]);

    $result = $this->service->generateAudio('hello world');

    expect($result)->toBeInstanceOf(UploadedFile::class)
        ->and($result->getClientOriginalName())->toContain('.wav');
});

/**
 * generateAudio throws when the synthesize endpoint responds with 5xx.
 */
it('test_generate_audio_throws_on_error', function (): void {
    Http::fake(['*/synthesize' => Http::response('Internal error', 500)]);

    expect(fn () => $this->service->generateAudio('hello'))
        ->toThrow(\Exception::class, 'Failed to generate audio');
});

/**
 * generateAudio appends a period when the text lacks ending punctuation.
 */
it('test_generate_audio_punctuation', function (string $input, string $expectedSuffix): void {
    Http::fake([
        '*/synthesize' => function (\Illuminate\Http\Client\Request $request) use ($expectedSuffix) {
            expect($request->data()['text'])->toEndWith($expectedSuffix);

            return Http::response(str_repeat('x', 100), 200, ['content-type' => 'audio/wav']);
        },
    ]);

    $this->service->generateAudio($input);
})->with(dataProviderForTestGenerateAudioPunctuation());

/**
 * Provides input strings and expected suffix characters for testForGenerateAudioPunctuation.
 */
function dataProviderForTestGenerateAudioPunctuation(): array
{
    return [
        'no punctuation → period appended' => ['hello world', '.'],
        'already has period → unchanged' => ['Hello world.', '.'],
    ];
}
