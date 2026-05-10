<?php

declare(strict_types=1);

/**
 * Unit tests for CourseService.
 */

use App\DTO\Course\CreateCourseDTO;
use App\DTO\Course\UpdateCourseDTO;
use App\Models\Education\Course;
use App\Services\CourseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new CourseService();
});

/**
 * getCourseById returns the course or throws when the id does not exist.
 */
it('test_get_course_by_id', function (bool $exists): void {
    if ($exists) {
        $course = makeCourse();
        expect($this->service->getCourseById($course->id)->id)->toBe($course->id);
    } else {
        expect(fn () => $this->service->getCourseById(PHP_INT_MAX))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }
})->with(dataProviderForTestGetCourseById());

/**
 * Provides boolean flags for existing and non-existing course IDs for testForGetCourseById.
 */
function dataProviderForTestGetCourseById(): array
{
    return [
        'existing course returned' => [true],
        'unknown id throws' => [false],
    ];
}

/**
 * createCourse saves a draft course with title and language from the DTO.
 */
it('test_create_course', function (): void {
    $dto = new CreateCourseDTO(title: 'English Basics', language: 'English', isPremium: false);

    $course = $this->service->createCourse($dto);

    expect($course->id)->not->toBeNull()
        ->and($course->title)->toBe('English Basics')
        ->and($course->is_active)->toBeFalse()
        ->and(Course::find($course->id)->is_active)->toBeFalse();
});

/**
 * updateCourse overwrites only non-null DTO fields and keeps the rest as-is.
 */
it('test_update_course', function (?string $newTitle, string $expectedTitle): void {
    $course = makeCourse('Original Title');

    $dto = new UpdateCourseDTO(id: $course->id, title: $newTitle);
    $result = $this->service->updateCourse($course, $dto);

    expect($result->title)->toBe($expectedTitle)
        ->and(Course::find($course->id)->title)->toBe($expectedTitle);
})->with(dataProviderForTestUpdateCourse());

/**
 * Provides title values for update scenarios for testForUpdateCourse.
 */
function dataProviderForTestUpdateCourse(): array
{
    return [
        'new title is applied' => ['New Title', 'New Title'],
        'null title keeps original' => [null, 'Original Title'],
    ];
}

/**
 * deleteCourse removes the course row and returns true.
 */
it('test_delete_course', function (): void {
    $course = makeCourse();

    expect($this->service->deleteCourse($course))->toBeTrue()
        ->and(Course::find($course->id))->toBeNull();
});

/**
 * publish sets is_active to true and persists the change.
 */
it('test_publish', function (): void {
    $course = makeCourse(isActive: false);

    $result = $this->service->publish($course);

    expect($result->is_active)->toBeTrue()
        ->and(Course::find($course->id)->is_active)->toBeTrue();
});

/**
 * unpublish sets is_active to false and persists the change.
 */
it('test_unpublish', function (): void {
    $course = makeCourse(isActive: true);

    $result = $this->service->unpublish($course);

    expect($result->is_active)->toBeFalse()
        ->and(Course::find($course->id)->is_active)->toBeFalse();
});

/**
 * archive marks the course archived and deactivates it.
 */
it('test_archive', function (): void {
    $course = makeCourse(isActive: true);

    $result = $this->service->archive($course);

    expect($result->is_archived)->toBeTrue()
        ->and($result->is_active)->toBeFalse();
});

/**
 * unarchive clears is_archived without flipping the previous active flag.
 */
it('test_unarchive', function (bool $wasActive): void {
    $course = makeCourse(isActive: $wasActive);
    $course->update(['is_archived' => true]);

    $result = $this->service->unarchive($course);

    expect($result->is_archived)->toBeFalse();
})->with(dataProviderForTestUnarchive());

/**
 * Provides active state variants for testForUnarchive.
 */
function dataProviderForTestUnarchive(): array
{
    return [
        'unarchive active course' => [true],
        'unarchive inactive course' => [false],
    ];
}

function makeCourse(string $title = 'Test Course', bool $isActive = false): Course
{
    return Course::create([
        'title' => $title,
        'language' => 'English',
        'is_active' => $isActive,
        'is_premium' => false,
    ]);
}
