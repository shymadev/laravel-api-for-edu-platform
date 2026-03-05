<?php

declare(strict_types=1);

use App\Http\Controllers\LessonController;
use App\Models\Education\Lesson;
use App\Services\Contracts\Lesson\LessonServiceInterface;

beforeEach(function () {
    $this->lessonService = Mockery::mock(LessonServiceInterface::class);
    $this->controller = new LessonController($this->lessonService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls lessonService when deleting lesson', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();

    $this->lessonService->shouldReceive('deleteLesson')
        ->with($lesson)
        ->once()
        ->andReturn(true);

    // Verify service is called correctly
    $result = $this->lessonService->deleteLesson($lesson);

    expect($result)->toBeTrue();
});

test('controller calls lessonService to publish lesson', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();

    $this->lessonService->shouldReceive('publish')
        ->with($lesson)
        ->once()
        ->andReturn($lesson);

    $result = $this->lessonService->publish($lesson);

    expect($result)->toBe($lesson);
});

test('controller calls lessonService to unpublish lesson', function () {
    $lesson = Mockery::mock(Lesson::class)->makePartial();
    $lesson->shouldAllowMockingProtectedMethods();

    $this->lessonService->shouldReceive('unpublish')
        ->with($lesson)
        ->once()
        ->andReturn($lesson);

    $result = $this->lessonService->unpublish($lesson);

    expect($result)->toBe($lesson);
});
