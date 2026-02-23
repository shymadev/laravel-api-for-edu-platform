<?php

declare(strict_types=1);

use App\DTO\Lesson\UpdateLessonDTO;
use App\Models\Education\Lesson;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use App\Services\Lesson\LessonService;

beforeEach(function () {
    $this->audioStorage = Mockery::mock(AudioStorageInterface::class);
    $this->ttsService = Mockery::mock(TTSServiceInterface::class);
    $this->service = new LessonService($this->audioStorage, $this->ttsService);
});

afterEach(function () {
    Mockery::close();
});

test('validateContent returns empty array for null content', function () {
    $result = $this->service->validateContent(null);

    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});

test('validateContent returns errors for missing type field', function () {
    $invalidContent = [
        ['data' => 'Some text'], // missing 'type'
    ];

    $result = $this->service->validateContent($invalidContent);

    expect($result)->toBeArray();
    expect($result)->not->toBeEmpty();
    expect($result[0])->toContain("missing 'type' field");
});

test('publish marks lesson as active', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();
    
    $lesson->shouldReceive('update')->once()->with(['is_active' => true])->andReturn(true);
    $lesson->shouldReceive('save')->once()->andReturn(true);
    $lesson->shouldReceive('refresh')->once()->andReturn($lesson);

    $result = $this->service->publish($lesson);

    expect($result)->toBeInstanceOf(Lesson::class);
});

test('unpublish marks lesson as inactive', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();
    
    $lesson->shouldReceive('update')->once()->with(['is_active' => false])->andReturn(true);
    $lesson->shouldReceive('save')->once()->andReturn(true);
    $lesson->shouldReceive('refresh')->once()->andReturn($lesson);

    $result = $this->service->unpublish($lesson);

    expect($result)->toBeInstanceOf(Lesson::class);
});

test('deleteLesson returns true on successful deletion', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();
    
    $lesson->shouldReceive('delete')->once()->andReturn(true);

    $result = $this->service->deleteLesson($lesson);

    expect($result)->toBeTrue();
});
