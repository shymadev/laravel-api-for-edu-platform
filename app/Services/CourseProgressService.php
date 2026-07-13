<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Progress\CourseProgressDTO;
use App\DTO\Progress\UserStatisticsDTO;
use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\User\UserCompletedLesson;
use App\Models\User\UserLessonBlockProgress;
use App\Models\User\UserStoppedCourse;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing course progress.
 */
#[Singleton]
class CourseProgressService
{
    /**
     * Get a list of lessons completed by the user.
     *
     * @param int $userId
     *
     * @return Collection<int, UserCompletedLesson>
     */
    public function completedLessons(int $userId): Collection
    {
        return UserCompletedLesson::query()
            ->where('user_id', $userId)
            ->with('lesson')
            ->get();
    }

    /**
     * Mark a lesson as complete for the user.
     *
     * @param int $userId
     * @param int $lessonId
     *
     * @return boolean
     */
    public function markLessonComplete(int $userId, int $lessonId): bool
    {
        $exists = UserCompletedLesson::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->exists();

        if ($exists) {
            return false;
        }

        UserCompletedLesson::create([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
        ]);

        return true;
    }

    /**
     * Get the progress for a specific course.
     *
     * @param int $userId
     * @param int $courseId
     *
     * @return CourseProgressDTO
     */
    public function courseProgress(int $userId, int $courseId): CourseProgressDTO
    {
        return Cache::tags(['progress', "user.{$userId}.progress", "course.{$courseId}"])->remember(
            "user.{$userId}.course.{$courseId}.progress",
            300,
            function () use ($userId, $courseId): CourseProgressDTO {
                $course = Course::find($courseId);

                $totalLessons = Lesson::whereHas('topic', function (Builder $query) use ($courseId): void {
                    $query->where('course_id', $courseId);
                })->count();

                $completedLessons = UserCompletedLesson::where('user_id', $userId)
                    ->whereHas('lesson.topic', function (Builder $query) use ($courseId): void {
                        $query->where('course_id', $courseId);
                    })->count();

                return $this->createProgressDTO($course, $totalLessons, $completedLessons);
            },
        );
    }

    /**
     * Get the progress for all courses the user has started.
     *
     * @param int $userId
     *
     * @return Collection<int, CourseProgressDTO>
     */
    public function userCoursesProgress(int $userId): Collection
    {
        $courses = Course::all();

        $stoppedCourseIds = UserStoppedCourse::query()
            ->where('user_id', $userId)
            ->pluck('course_id')
            ->flip()
            ->all();

        return $courses->map(function (Course $course) use ($userId, $stoppedCourseIds) {
            $courseId = $course->id;

            $totalLessons = Lesson::whereHas('topic', function (Builder $query) use ($courseId): void {
                $query->where('course_id', $courseId);
            })->count();

            $completedLessons = UserCompletedLesson::where('user_id', $userId)
                ->whereHas('lesson.topic', function (Builder $query) use ($courseId): void {
                    $query->where('course_id', $courseId);
                })->count();

            return $this->createProgressDTO($course, $totalLessons, $completedLessons, isset($stoppedCourseIds[$courseId]));
        });
    }

    /**
     * Get the statistics for the user.
     *
     * @param int $userId
     *
     * @return UserStatisticsDTO
     */
    public function userStatistics(int $userId): UserStatisticsDTO
    {
        return Cache::tags(['progress', "user.{$userId}.progress"])->remember(
            "user.{$userId}.statistics",
            300,
            function () use ($userId): UserStatisticsDTO {
                $coursesProgress = $this->userCoursesProgress($userId);

                $totalCompletedLessons = UserCompletedLesson::where('user_id', $userId)->count();

                $completedCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->isCompleted)->count();
                $inProgressCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->completedLessons > 0 && !$dto->isCompleted && !$dto->isStopped)->count();

                $startedCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->completedLessons > 0 && !$dto->isStopped);
                $averageProgress = $startedCourses->count() > 0
                    ? $startedCourses->avg('progressPercentage')
                    : 0.0;

                $totalCompletedExercises = UserLessonBlockProgress::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->where('block_type', '!=', 'audio')
                    ->count();

                $totalListenedAudio = UserLessonBlockProgress::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->where('block_type', 'audio')
                    ->count();

                return new UserStatisticsDTO(
                    totalCompletedLessons: $totalCompletedLessons,
                    totalCompletedCourses: $completedCourses,
                    totalInProgressCourses: $inProgressCourses,
                    averageProgress: (float) $averageProgress,
                    totalCompletedExercises: $totalCompletedExercises,
                    totalListenedAudio: $totalListenedAudio,
                );
            },
        );
    }

    /**
     * Get the block progress for a lesson.
     *
     * @param int $userId
     * @param int $lessonId
     *
     * @return array{completed: array<int>, states: array<int, array<string, mixed>>}
     */
    public function lessonBlockProgress(int $userId, int $lessonId): array
    {
        $records = UserLessonBlockProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->get(['block_index', 'is_completed', 'block_state']);

        $completed = [];
        $states = [];

        foreach ($records as $record) {
            if ($record->is_completed) {
                $completed[] = $record->block_index;
            }

            if ($record->block_state !== null) {
                $states[$record->block_index] = $record->block_state;
            }
        }

        return ['completed' => $completed, 'states' => $states];
    }

    /**
     * Save the block progress for a lesson.
     *
     * @param int $userId
     * @param int $lessonId
     * @param int $blockIndex
     * @param string $blockType
     * @param bool $isCompleted
     * @param array<string, mixed>|null $blockState
     *
     * @return void
     */
    public function saveBlockProgress(
        int $userId,
        int $lessonId,
        int $blockIndex,
        string $blockType,
        bool $isCompleted = false,
        ?array $blockState = null,
    ): void {
        DB::transaction(function () use ($userId, $lessonId, $blockIndex, $blockType, $isCompleted, $blockState): void {
            $record = UserLessonBlockProgress::query()
                ->where('user_id', $userId)
                ->where('lesson_id', $lessonId)
                ->where('block_index', $blockIndex)
                ->lockForUpdate()
                ->first();

            if ($record === null) {
                UserLessonBlockProgress::create([
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                    'block_index' => $blockIndex,
                    'block_type' => $blockType,
                    'is_completed' => $isCompleted,
                    'block_state' => $blockState,
                ]);

                return;
            }

            if ($record->is_completed && !$isCompleted) {
                return;
            }

            $record->block_type = $blockType;

            if ($isCompleted) {
                $record->is_completed = true;
                if ($blockState !== null || $record->block_state === null) {
                    $record->block_state = $blockState;
                }
            } else {
                $record->block_state = $blockState;
            }

            $record->save();
        });
    }

    /**
     * Stop a course for the user.
     *
     * @param int $userId
     * @param int $courseId
     *
     * @return void
     */
    public function stopCourse(int $userId, int $courseId): void
    {
        UserStoppedCourse::firstOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['stopped_at' => now()],
        );

        Cache::tags(["user.{$userId}.progress", 'progress'])->flush();
    }

    /**
     * Resume a course for the user.
     *
     * @param int $userId
     * @param int $courseId
     *
     * @return void
     */
    public function resumeCourse(int $userId, int $courseId): void
    {
        UserStoppedCourse::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->delete();

        Cache::tags(["user.{$userId}.progress", 'progress'])->flush();
    }

    /**
     * Create a progress DTO.
     *
     * @param ?Course $course
     * @param int $totalLessons
     * @param int $completedLessons
     * @param bool $isStopped
     *
     * @return CourseProgressDTO
     */
    protected function createProgressDTO(?Course $course, int $totalLessons, int $completedLessons, bool $isStopped = false): CourseProgressDTO
    {
        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
        $isCompleted = $totalLessons > 0 && $completedLessons >= $totalLessons;
        $courseId = $course !== null ? $course->id : 0;
        $title = $course !== null ? $course->title : 'Unknown Course';
        $previewImage = $course !== null ? $course->preview_image : null;

        return new CourseProgressDTO(
            courseId: $courseId,
            title: $title,
            previewImageUrl: $previewImage,
            totalLessons: $totalLessons,
            completedLessons: $completedLessons,
            progressPercentage: round((float) $progress, 2),
            isCompleted: $isCompleted,
            isStopped: $isStopped,
        );
    }
}
