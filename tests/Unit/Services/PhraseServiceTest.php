<?php

declare(strict_types=1);

/**
 * Unit tests for PhraseService.
 */

use App\DTO\Phrase\CreatePhraseDTO;
use App\DTO\Phrase\UpdatePhraseDTO;
use App\Models\Education\Phrase;
use App\Services\EspokeTranscriptionService;
use App\Services\PhraseService;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tts = Mockery::mock(TTSService::class);
    $this->audio = Mockery::mock(AudioStorageService::class);
    $this->transcription = Mockery::mock(EspokeTranscriptionService::class);
    $this->service = new PhraseService($this->tts, $this->audio, $this->transcription);
});

/**
 * getAllCategories lists distinct non-null topics, or none when all are null.
 */
it('test_get_all_categories', function (array $topics, int $expectedDistinct): void {
    foreach ($topics as $topic) {
        Phrase::create(['text' => fake()->unique()->word(), 'is_phrasebook' => true, 'topic' => $topic]);
    }

    $result = $this->service->getAllCategories();

    expect($result)->toHaveCount($expectedDistinct);
})->with(dataProviderForTestGetAllCategories());

/**
 * Provides topic arrays and expected distinct counts for testForGetAllCategories.
 */
function dataProviderForTestGetAllCategories(): array
{
    return [
        'two distinct topics' => [['greetings', 'travel'], 2],
        'null topic excluded' => [[null, null], 0],
    ];
}

/**
 * getPhraseById returns the phrase or throws when the id is unknown.
 */
it('test_get_phrase_by_id', function (bool $exists): void {
    if ($exists) {
        $phrase = Phrase::create(['text' => 'Hello', 'is_phrasebook' => true]);
        expect($this->service->getPhraseById($phrase->id)->id)->toBe($phrase->id);
    } else {
        expect(fn () => $this->service->getPhraseById(PHP_INT_MAX))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }
})->with(dataProviderForTestGetPhraseById());

/**
 * Provides boolean flags for existing and non-existing phrase IDs for testForGetPhraseById.
 */
function dataProviderForTestGetPhraseById(): array
{
    return [
        'existing phrase returned' => [true],
        'unknown id throws' => [false],
    ];
}

/**
 * createPhrase stores the row, generates audio via TTS, uploads, and transcribes.
 */
it('test_create_phrase', function (): void {
    $fakeFile = UploadedFile::fake()->create('audio.wav', 10, 'audio/wav');

    $this->tts->shouldReceive('generateAudio')->once()->with('Hello')->andReturn($fakeFile);
    $this->audio->shouldReceive('upload')->once()->with($fakeFile, 'phrases')->andReturn('https://storage.example.com/audio.wav');
    $this->transcription->shouldReceive('transcribe')->once()->with('Hello')->andReturn('HH AH L OW');

    $dto = new CreatePhraseDTO(text: 'Hello', translation: 'Привет');
    $phrase = $this->service->createPhrase($dto);

    expect($phrase->text)->toBe('Hello')
        ->and($phrase->audio)->toBe('https://storage.example.com/audio.wav')
        ->and($phrase->transcription)->toBe('HH AH L OW');
});

/**
 * updatePhrase regenerates audio when text changes; translation-only skips audio.
 */
it('test_update_phrase', function (bool $textChanged): void {
    $phrase = Phrase::create([
        'text' => 'Hello',
        'translation' => 'Привет',
        'audio' => 'https://storage.example.com/old.wav',
        'is_phrasebook' => true,
    ]);

    if ($textChanged) {
        $fakeFile = UploadedFile::fake()->create('new.wav', 10, 'audio/wav');

        $this->tts->shouldReceive('generateAudio')->once()->with('Hi')->andReturn($fakeFile);
        $this->audio->shouldReceive('upload')->once()->with($fakeFile, 'phrases')->andReturn('https://storage.example.com/new.wav');
        $this->audio->shouldReceive('delete')->once()->with('https://storage.example.com/old.wav');
        $this->transcription->shouldReceive('transcribe')->once()->with('Hi')->andReturn('HH AY');

        $dto = new UpdatePhraseDTO(text: 'Hi');
        $result = $this->service->updatePhrase($phrase, $dto);

        expect($result->text)->toBe('Hi');
    } else {
        $dto = new UpdatePhraseDTO(translation: 'Привет обновлено');
        $result = $this->service->updatePhrase($phrase, $dto);

        expect($result->translation)->toBe('Привет обновлено');
    }
})->with(dataProviderForTestUpdatePhrase());

/**
 * Provides text-change flags for testForUpdatePhrase.
 */
function dataProviderForTestUpdatePhrase(): array
{
    return [
        'text changed → audio regenerated' => [true],
        'only translation changed → no audio regeneration' => [false],
    ];
}

/**
 * deletePhrase removes the row and deletes stored audio when a URL exists.
 */
it('test_delete_phrase', function (bool $hasAudio): void {
    $phrase = Phrase::create([
        'text' => 'Hello',
        'is_phrasebook' => true,
        'audio' => $hasAudio ? 'https://storage.example.com/audio.wav' : null,
    ]);

    if ($hasAudio) {
        $this->audio->shouldReceive('delete')->once()->with('https://storage.example.com/audio.wav');
    } else {
        $this->audio->shouldNotReceive('delete');
    }

    $this->service->deletePhrase($phrase->id);

    expect(Phrase::find($phrase->id))->toBeNull();
})->with(dataProviderForTestDeletePhrase());

/**
 * Provides audio existence flags for testForDeletePhrase.
 */
function dataProviderForTestDeletePhrase(): array
{
    return [
        'phrase with audio → storage delete called' => [true],
        'phrase without audio → no storage call' => [false],
    ];
}

/**
 * resolveByText reuses a case-insensitive match or creates a non-phrasebook row.
 */
it('test_resolve_by_text', function (bool $alreadyExists): void {
    if ($alreadyExists) {
        $existing = Phrase::create(['text' => 'hello', 'is_phrasebook' => true]);

        $this->tts->shouldNotReceive('generateAudio');

        $result = $this->service->resolveByText('Hello', 'Привет');

        expect($result->id)->toBe($existing->id);
    } else {
        $fakeFile = UploadedFile::fake()->create('audio.wav', 10, 'audio/wav');

        $this->tts->shouldReceive('generateAudio')->once()->andReturn($fakeFile);
        $this->audio->shouldReceive('upload')->once()->andReturn('https://storage.example.com/audio.wav');
        $this->transcription->shouldReceive('transcribe')->once()->andReturn('HH AH L OW');

        $result = $this->service->resolveByText('brand new phrase', 'новая фраза');

        expect($result->text)->toBe('brand new phrase')
            ->and($result->is_phrasebook)->toBeFalse();
    }
})->with(dataProviderForTestResolveByText());

/**
 * Provides existence flags for testForResolveByText.
 */
function dataProviderForTestResolveByText(): array
{
    return [
        'existing phrase found by text (case-insensitive)' => [true],
        'no match → new non-phrasebook phrase created' => [false],
    ];
}
