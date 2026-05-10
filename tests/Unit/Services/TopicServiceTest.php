<?php

declare(strict_types=1);

/**
 * Unit tests for TopicService.
 */

use App\DTO\Topic\CreateTopicDTO;
use App\DTO\Topic\UpdateTopicDTO;
use App\Models\Education\Course;
use App\Models\Education\Topic;
use App\Services\TopicService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new TopicService();
    $this->course = Course::create([
        'title' => 'Test Course',
        'language' => 'en',
        'is_active' => true,
        'is_premium' => false,
    ]);
});

/**
 * getTopicById returns the topic or throws when the id does not exist.
 */
it('test_get_topic_by_id', function (bool $exists): void {
    if ($exists) {
        $topic = makeTopicFor($this->course->id);
        expect($this->service->getTopicById($topic->id)->id)->toBe($topic->id);
    } else {
        expect(fn () => $this->service->getTopicById(PHP_INT_MAX))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }
})->with(dataProviderForTestGetTopicById());

/**
 * Provides boolean flags for existing and non-existing topic IDs for testForGetTopicById.
 */
function dataProviderForTestGetTopicById(): array
{
    return [
        'existing topic returned' => [true],
        'unknown id throws' => [false],
    ];
}

/**
 * createTopic saves a draft topic tied to the course from the DTO.
 */
it('test_create_topic', function (): void {
    $dto = new CreateTopicDTO(courseId: $this->course->id, title: 'Grammar Basics');

    $topic = $this->service->createTopic($dto);

    expect($topic->id)->not->toBeNull()
        ->and($topic->title)->toBe('Grammar Basics')
        ->and($topic->is_active)->toBeFalse()
        ->and(Topic::find($topic->id)->is_active)->toBeFalse();
});

/**
 * updateTopic overwrites only non-null DTO fields and keeps the rest as-is.
 */
it('test_update_topic', function (?string $newTitle, string $expectedTitle): void {
    $topic = makeTopicFor($this->course->id, 'Original');

    $dto = new UpdateTopicDTO(id: $topic->id, title: $newTitle);
    $result = $this->service->updateTopic($topic, $dto);

    expect($result->title)->toBe($expectedTitle);
})->with(dataProviderForTestUpdateTopic());

/**
 * Provides title values for update scenarios for testForUpdateTopic.
 */
function dataProviderForTestUpdateTopic(): array
{
    return [
        'new title applied' => ['Updated', 'Updated'],
        'null title keeps original' => [null, 'Original'],
    ];
}

/**
 * deleteTopic removes the topic row and returns true.
 */
it('test_delete_topic', function (): void {
    $topic = makeTopicFor($this->course->id);

    expect($this->service->deleteTopic($topic))->toBeTrue()
        ->and(Topic::find($topic->id))->toBeNull();
});

/**
 * getTopicsByCourse lists every topic on the course, or none when empty.
 */
it('test_get_topics_by_course', function (int $count): void {
    for ($i = 0; $i < $count; $i++) {
        makeTopicFor($this->course->id, "Topic {$i}");
    }

    expect($this->service->getTopicsByCourse($this->course->id))->toHaveCount($count);
})->with(dataProviderForTestGetTopicsByCourse());

/**
 * Provides topic counts for testForGetTopicsByCourse.
 */
function dataProviderForTestGetTopicsByCourse(): array
{
    return [
        'no topics → empty collection' => [0],
        'two topics → two items' => [2],
    ];
}

/**
 * publish sets is_active to true and persists the change.
 */
it('test_publish', function (): void {
    $topic = makeTopicFor($this->course->id, isActive: false);

    $result = $this->service->publish($topic);

    expect($result->is_active)->toBeTrue()
        ->and(Topic::find($topic->id)->is_active)->toBeTrue();
});

/**
 * unpublish sets is_active to false and persists the change.
 */
it('test_unpublish', function (): void {
    $topic = makeTopicFor($this->course->id, isActive: true);

    $result = $this->service->unpublish($topic);

    expect($result->is_active)->toBeFalse()
        ->and(Topic::find($topic->id)->is_active)->toBeFalse();
});

function makeTopicFor(int $courseId, string $title = 'Test Topic', bool $isActive = false): Topic
{
    return Topic::create([
        'course_id' => $courseId,
        'title' => $title,
        'is_active' => $isActive,
    ]);
}
