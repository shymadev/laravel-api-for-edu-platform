<?php

declare(strict_types=1);

use App\DTO\Course\UpdateCourseDTO;
use App\Models\Education\Course;
use App\Services\Course\CourseService;

beforeEach(function () {
    $this->service = new CourseService();
});

afterEach(function () {
    Mockery::close();
});

test('updateCourse method updates course', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->id = 1;
    $course->title = 'Old Title';

    $dto = new UpdateCourseDTO(
        id: 1,
        title: 'New Title'
    );

    $course->shouldReceive('update')->once()->andReturn(true);
    $course->shouldReceive('refresh')->once()->andReturn($course);

    $result = $this->service->updateCourse($course, $dto);

    expect($result)->toBeInstanceOf(Course::class);
});

test('deleteCourse method deletes course', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->id = 1;
    $course->preview_image = null;

    $course->shouldReceive('delete')->once()->andReturn(true);

    $result = $this->service->deleteCourse($course);

    expect($result)->toBeTrue();
});

test('publish method marks course as published', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->id = 1;
    $course->is_active = false;

    $course->shouldReceive('update')->with(['is_active' => true])->once()->andReturn(true);
    $course->shouldReceive('save')->once()->andReturn(true);
    $course->shouldReceive('refresh')->once()->andReturn($course);

    $result = $this->service->publish($course);

    expect($result)->toBeInstanceOf(Course::class);
});

test('unpublish method marks course as unpublished', function () {
    $course = Mockery::mock(Course::class)->makePartial();
    $course->id = 1;
    $course->is_active = true;

    $course->shouldReceive('update')->with(['is_active' => false])->once()->andReturn(true);
    $course->shouldReceive('save')->once()->andReturn(true);
    $course->shouldReceive('refresh')->once()->andReturn($course);

    $result = $this->service->unpublish($course);

    expect($result)->toBeInstanceOf(Course::class);
});
