<?php

declare(strict_types=1);

/**
 * Unit tests for LessonService.
 */

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Lesson\LessonService;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $audioStorage = Mockery::mock(AudioStorageService::class);
    $ttsService = Mockery::mock(TTSService::class);
    $this->service = new LessonService($audioStorage, $ttsService);
});

/**
 * validateContent: null/empty is valid; entries without type yield errors.
 */
it('test_validate_content', function (?array $content, int $expectedErrors): void {
    expect($this->service->validateContent($content))->toHaveCount($expectedErrors);
})->with(dataProviderForTestValidateContent());

/**
 * Provides content arrays and expected error counts for testForValidateContent.
 */
function dataProviderForTestValidateContent(): array
{
    return [
        'null content → no errors' => [null, 0],
        'empty array → no errors' => [[], 0],
    ];
}

/**
 * validateContent reports missing 'type' when a paragraph omits it.
 */
it('test_validate_content_missing_type', function (): void {
    $errors = $this->service->validateContent([['text' => 'no type field']]);

    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toContain("missing 'type'");
});

/**
 * validateContent accepts valid text blocks and flags video blocks without url.
 */
it('test_validate_content_paragraph_types', function (array $paragraph, bool $expectError): void {
    $errors = $this->service->validateContent([$paragraph]);

    if ($expectError) {
        expect($errors)->not->toBeEmpty();
    } else {
        expect($errors)->toBe([]);
    }
})->with(dataProviderForTestValidateContentParagraphTypes());

/**
 * Provides paragraph definitions and error expectation flags for testForValidateContentParagraphTypes.
 */
function dataProviderForTestValidateContentParagraphTypes(): array
{
    return [
        'valid text paragraph' => [['type' => 'text', 'content' => 'Hello'], false],
        'video paragraph missing url' => [['type' => 'video'], true],
    ];
}

/**
 * getLessonById returns the lesson or throws when the id does not exist.
 */
it('test_get_lesson_by_id', function (bool $exists): void {
    if ($exists) {
        $lesson = makeTestLesson();
        expect($this->service->getLessonById($lesson->id)->id)->toBe($lesson->id);
    } else {
        expect(fn () => $this->service->getLessonById(PHP_INT_MAX))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }
})->with(dataProviderForTestGetLessonById());

/**
 * Provides boolean flags for existing and non-existing lesson IDs for testForGetLessonById.
 */
function dataProviderForTestGetLessonById(): array
{
    return [
        'existing lesson returned' => [true],
        'unknown id throws' => [false],
    ];
}

/**
 * getLessonsByTopic returns every lesson for the given topic.
 */
it('test_get_lessons_by_topic', function (): void {
    $course = Course::create(['title' => 'C', 'language' => 'en', 'is_active' => true, 'is_premium' => false]);
    $topic = Topic::create(['course_id' => $course->id, 'title' => 'T', 'is_active' => true]);

    Lesson::create(['topic_id' => $topic->id, 'title' => 'L1', 'weight' => 2, 'is_active' => true, 'content' => []]);
    Lesson::create(['topic_id' => $topic->id, 'title' => 'L2', 'weight' => 1, 'is_active' => true, 'content' => []]);

    expect($this->service->getLessonsByTopic($topic))->toHaveCount(2);
});

/**
 * deleteLesson removes the lesson row and returns true.
 */
it('test_delete_lesson', function (): void {
    $lesson = makeTestLesson();

    expect($this->service->deleteLesson($lesson))->toBeTrue()
        ->and(Lesson::find($lesson->id))->toBeNull();
});

/**
 * publish sets is_active to true and persists the change.
 */
it('test_publish', function (): void {
    $lesson = makeTestLesson(isActive: false);

    $result = $this->service->publish($lesson);

    expect($result->is_active)->toBeTrue()
        ->and(Lesson::find($lesson->id)->is_active)->toBeTrue();
});

/**
 * unpublish sets is_active to false and persists the change.
 */
it('test_unpublish', function (): void {
    $lesson = makeTestLesson(isActive: true);

    $result = $this->service->unpublish($lesson);

    expect($result->is_active)->toBeFalse()
        ->and(Lesson::find($lesson->id)->is_active)->toBeFalse();
});

function makeTestLesson(bool $isActive = true): Lesson
{
    $course = Course::create(['title' => 'C', 'language' => 'en', 'is_active' => true, 'is_premium' => false]);
    $topic = Topic::create(['course_id' => $course->id, 'title' => 'T', 'is_active' => true]);

    return Lesson::create([
        'topic_id' => $topic->id,
        'title' => 'Test Lesson',
        'weight' => 1,
        'is_active' => $isActive,
        'content' => [],
    ]);
}
