<?php

declare(strict_types=1);

use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use App\Services\Phrase\PhraseService;

beforeEach(function () {
    $this->ttsService = Mockery::mock(TTSServiceInterface::class);
    $this->audioStorage = Mockery::mock(AudioStorageInterface::class);
    $this->service = new PhraseService($this->ttsService, $this->audioStorage);
});

afterEach(function () {
    Mockery::close();
});

test('PhraseService can be instantiated with dependencies', function () {
    expect($this->service)->toBeInstanceOf(PhraseService::class);
    expect(method_exists($this->service, 'getAllCategories'))->toBeTrue();
    expect(method_exists($this->service, 'createPhrase'))->toBeTrue();
});
