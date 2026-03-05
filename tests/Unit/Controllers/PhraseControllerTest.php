<?php

declare(strict_types=1);

use App\Http\Controllers\PhraseController;
use App\Models\Education\Phrase;
use App\Services\Contracts\Education\PhraseServiceInterface;

beforeEach(function () {
    $this->phraseService = Mockery::mock(PhraseServiceInterface::class);
    $this->controller = new PhraseController($this->phraseService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls phraseService to get all categories', function () {
    $categories = ['Greetings', 'Numbers', 'Colors'];

    $this->phraseService->shouldReceive('getAllCategories')
        ->once()
        ->andReturn($categories);

    $result = $this->phraseService->getAllCategories();

    expect($result)->toBe($categories);
    expect($result)->toHaveCount(3);
});

test('controller calls phraseService to get phrase by id', function () {
    $phraseId = 1;
    $phrase = Mockery::mock(Phrase::class)->makePartial();
    $phrase->shouldAllowMockingProtectedMethods();

    $this->phraseService->shouldReceive('getPhraseById')
        ->with($phraseId)
        ->once()
        ->andReturn($phrase);

    $result = $this->phraseService->getPhraseById($phraseId);

    expect($result)->toBe($phrase);
});

test('controller calls phraseService to delete phrase', function () {
    $phraseId = 1;

    $this->phraseService->shouldReceive('deletePhrase')
        ->with($phraseId)
        ->once();

    $this->phraseService->deletePhrase($phraseId);

    // Test passes if no exception is thrown
    expect(true)->toBeTrue();
});

test('controller calls phraseService to regenerate audio', function () {
    $phraseId = 1;
    $phrase = Mockery::mock(Phrase::class)->makePartial();
    $phrase->shouldAllowMockingProtectedMethods();

    $this->phraseService->shouldReceive('regenerateAudio')
        ->with($phraseId)
        ->once()
        ->andReturn($phrase);

    $result = $this->phraseService->regenerateAudio($phraseId);

    expect($result)->toBe($phrase);
});
