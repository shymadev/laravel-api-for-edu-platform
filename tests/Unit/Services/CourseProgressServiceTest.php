<?php

declare(strict_types=1);

/**
 * Unit tests for CourseProgressService.
 */

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Models\User\Role;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use App\Models\User\UserLessonBlockProgress;
use App\Models\User\UserStoppedCourse;
use App\Services\CourseProgressService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new CourseProgressService();

    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
        ],
        ['id'],
    );

    $this->user = User::create([
        'username' => 'progress_user',
        'email' => 'progress@example.com',
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $this->course = Course::create([
        'title' => 'Test Course',
        'language' => 'en',
        'is_active' => true,
        'is_premium' => false,
    ]);

    $this->topic = Topic::create([
        'course_id' => $this->course->id,
        'title' => 'Test Topic',
        'is_active' => true,
    ]);

    $this->lesson = Lesson::create([
        'topic_id' => $this->topic->id,
        'title' => 'Test Lesson',
        'weight' => 1,
        'is_active' => true,
        'content' => [],
    ]);
});

/**
 * completedLessons lists all completion rows for the user, or none when empty.
 */
it('test_completed_lessons', function (int $count): void {
    for ($i = 0; $i < $count; $i++) {
        $lesson = Lesson::create([
            'topic_id' => $this->topic->id,
            'title' => "Lesson {$i}",
            'weight' => $i,
            'is_active' => true,
            'content' => [],
        ]);
        UserCompletedLesson::create(['user_id' => $this->user->id, 'lesson_id' => $lesson->id]);
    }

    expect($this->service->completedLessons($this->user->id))->toHaveCount($count);
})->with(dataProviderForTestCompletedLessons());

/**
 * Provides completion counts for testForCompletedLessons.
 *
 * @return array<string, array<int>>
 */
function dataProviderForTestCompletedLessons(): array
{
    return [
        'no completions → empty collection' => [0],
        'two completions → two items' => [2],
    ];
}

/**
 * markLessonComplete returns true on first insert and false if already completed.
 */
it('test_mark_lesson_complete', function (bool $alreadyCompleted): void {
    if ($alreadyCompleted) {
        UserCompletedLesson::create(['user_id' => $this->user->id, 'lesson_id' => $this->lesson->id]);
    }

    $result = $this->service->markLessonComplete($this->user->id, $this->lesson->id);

    $count = UserCompletedLesson::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->count();

    expect($result)->toBe(!$alreadyCompleted)
        ->and($count)->toBe(1);
})->with(dataProviderForTestMarkLessonComplete());

/**
 * Provides pre-completion state scenarios for testForMarkLessonComplete.
 *
 * @return array<string, array<bool>>
 */
function dataProviderForTestMarkLessonComplete(): array
{
    return [
        'first completion → creates record, returns true' => [false],
        'already completed → skips, returns false' => [true],
    ];
}

/**
 * courseProgress exposes totals, completed count, percentage, and completion flag.
 */
it('test_course_progress', function (): void {
    UserCompletedLesson::create(['user_id' => $this->user->id, 'lesson_id' => $this->lesson->id]);

    $dto = $this->service->courseProgress($this->user->id, $this->course->id);

    expect($dto->totalLessons)->toBe(1)
        ->and($dto->completedLessons)->toBe(1)
        ->and($dto->progressPercentage)->toBe(100.0)
        ->and($dto->isCompleted)->toBeTrue();
});

/**
 * lessonBlockProgress maps completed indexes and state, or empty when missing.
 */
it('test_lesson_block_progress', function (bool $hasRecords): void {
    if ($hasRecords) {
        UserLessonBlockProgress::create([
            'user_id' => $this->user->id,
            'lesson_id' => $this->lesson->id,
            'block_index' => 0,
            'block_type' => 'text',
            'is_completed' => true,
            'block_state' => ['answer' => 'yes'],
        ]);
    }

    $result = $this->service->lessonBlockProgress($this->user->id, $this->lesson->id);

    if ($hasRecords) {
        expect($result['completed'])->toContain(0)
            ->and($result['states'])->toHaveKey(0);
    } else {
        expect($result['completed'])->toBe([])
            ->and($result['states'])->toBe([]);
    }
})->with(dataProviderForTestLessonBlockProgress());

/**
 * Provides record existence flags for testForLessonBlockProgress.
 *
 * @return array<string, array<bool>>
 */
function dataProviderForTestLessonBlockProgress(): array
{
    return [
        'no records → empty arrays' => [false],
        'one completed block → correct indexes and states' => [true],
    ];
}

/**
 * saveBlockProgress inserts a row or updates an existing one for the block.
 */
it('test_save_block_progress', function (bool $alreadyExists): void {
    if ($alreadyExists) {
        UserLessonBlockProgress::create([
            'user_id' => $this->user->id,
            'lesson_id' => $this->lesson->id,
            'block_index' => 1,
            'block_type' => 'text',
            'is_completed' => false,
            'block_state' => null,
        ]);
    }

    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 1,
        blockType: 'text',
        isCompleted: true,
        blockState: ['key' => 'value'],
    );

    $record = UserLessonBlockProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->where('block_index', 1)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->is_completed)->toBeTrue()
        ->and($record->block_state)->toBe(['key' => 'value']);
})->with(dataProviderForTestSaveBlockProgress());

/**
 * Provides pre-existence flags for testForSaveBlockProgress.
 *
 * @return array<string, array<bool>>
 */
function dataProviderForTestSaveBlockProgress(): array
{
    return [
        'new record created' => [false],
        'existing record updated' => [true],
    ];
}

/**
 * completed block state is not overwritten by a delayed partial save.
 */
it('test_save_block_progress_keeps_completed_state_after_delayed_partial_save', function (): void {
    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 2,
        blockType: 'matching',
        isCompleted: true,
        blockState: ['final' => true],
    );

    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 2,
        blockType: 'matching',
        isCompleted: false,
        blockState: ['draft' => true],
    );

    $record = UserLessonBlockProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->where('block_index', 2)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->is_completed)->toBeTrue()
        ->and($record->block_state)->toBe(['final' => true]);
});

/**
 * completed save without state preserves an existing draft state.
 */
it('test_save_block_progress_preserves_existing_state_when_completed_state_is_null', function (): void {
    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 3,
        blockType: 'fill-gaps',
        isCompleted: false,
        blockState: ['draft' => 1],
    );

    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 3,
        blockType: 'fill-gaps',
        isCompleted: true,
        blockState: null,
    );

    $record = UserLessonBlockProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->where('block_index', 3)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->is_completed)->toBeTrue()
        ->and($record->block_state)->toBe(['draft' => 1]);
});

/**
 * incomplete block state still accepts normal partial updates.
 */
it('test_save_block_progress_updates_incomplete_partial_state', function (): void {
    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 4,
        blockType: 'translation',
        isCompleted: false,
        blockState: ['draft' => 1],
    );

    $this->service->saveBlockProgress(
        userId: $this->user->id,
        lessonId: $this->lesson->id,
        blockIndex: 4,
        blockType: 'translation',
        isCompleted: false,
        blockState: ['draft' => 2],
    );

    $record = UserLessonBlockProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->where('block_index', 4)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->is_completed)->toBeFalse()
        ->and($record->block_state)->toBe(['draft' => 2]);
});

/**
 * stopCourse creates at most one stopped-course row even when called twice.
 */
it('test_stop_course', function (): void {
    $this->service->stopCourse($this->user->id, $this->course->id);
    $this->service->stopCourse($this->user->id, $this->course->id);

    $count = UserStoppedCourse::where('user_id', $this->user->id)
        ->where('course_id', $this->course->id)
        ->count();

    expect($count)->toBe(1);
});

/**
 * resumeCourse deletes the stopped-course row so learning can continue.
 */
it('test_resume_course', function (): void {
    UserStoppedCourse::create([
        'user_id' => $this->user->id,
        'course_id' => $this->course->id,
        'stopped_at' => now(),
    ]);

    $this->service->resumeCourse($this->user->id, $this->course->id);

    expect(
        UserStoppedCourse::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)
            ->exists(),
    )->toBeFalse();
});
