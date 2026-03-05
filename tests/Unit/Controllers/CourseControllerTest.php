<?php

declare(strict_types=1);

use App\Http\Controllers\CourseController;
use App\Models\Education\Course;
use App\Services\Contracts\Course\CourseServiceInterface;
use App\Services\Storage\ImageStorageService;

beforeEach(function () {
    $this->courseService = Mockery::mock(CourseServiceInterface::class);
    $this->imageStorage = Mockery::mock(ImageStorageService::class);
    $this->controller = new CourseController($this->courseService, $this->imageStorage);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls courseService to get course by id', function () {
    $courseId = 1;
    $course = Mockery::mock(Course::class)->makePartial();
    $course->shouldAllowMockingProtectedMethods();

    $this->courseService->shouldReceive('getCourseById')
        ->with($courseId)
        ->once()
        ->andReturn($course);

    $result = $this->courseService->getCourseById($courseId);

    expect($result)->toBe($course);
});

test('controller calls courseService to publish course', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->shouldAllowMockingProtectedMethods();

    $this->courseService->shouldReceive('publish')
        ->with($course)
        ->once()
        ->andReturn($course);

    $result = $this->courseService->publish($course);

    expect($result)->toBe($course);
});

test('controller calls courseService to unpublish course', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->shouldAllowMockingProtectedMethods();

    $this->courseService->shouldReceive('unpublish')
        ->with($course)
        ->once()
        ->andReturn($course);

    $result = $this->courseService->unpublish($course);

    expect($result)->toBe($course);
});
