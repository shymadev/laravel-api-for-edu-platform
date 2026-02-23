<?php

declare(strict_types=1);

namespace App\Services\Progress;

use App\DTO\Progress\CourseProgressDTO;
use App\DTO\Progress\UserStatisticsDTO;
use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\User\UserCompletedLesson;
use App\Services\Contracts\Progress\CourseProgressServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CourseProgressService implements CourseProgressServiceInterface
{
    public function completedLessons(int $userId): Collection
    {
        return UserCompletedLesson::query()
            ->where('user_id', $userId)
            ->with('lesson')
            ->get();
    }

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

    public function courseProgress(int $userId, int $courseId): CourseProgressDTO
    {
        $course = Course::find($courseId);

        $totalLessons = Lesson::whereHas('topic', function (Builder $query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        $completedLessons = UserCompletedLesson::where('user_id', $userId)
            ->whereHas('lesson.topic', function (Builder $query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->count();

        return $this->createProgressDTO($course, $totalLessons, $completedLessons);
    }

    public function userCoursesProgress(int $userId): Collection
    {
        $courses = Course::all();

        return $courses->map(function (Course $course) use ($userId) {
            // Re-using logic but passing course object to avoid re-fetching
            $courseId = (int) $course->id;

            $totalLessons = Lesson::whereHas('topic', function (Builder $query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->count();

            $completedLessons = UserCompletedLesson::where('user_id', $userId)
                ->whereHas('lesson.topic', function (Builder $query) use ($courseId) {
                    $query->where('course_id', $courseId);
                })->count();

            return $this->createProgressDTO($course, $totalLessons, $completedLessons);
        });
    }

    public function userStatistics(int $userId): UserStatisticsDTO
    {
        $coursesProgress = $this->userCoursesProgress($userId);

        $totalCompletedLessons = UserCompletedLesson::where('user_id', $userId)->count();

        $completedCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->isCompleted)->count();
        $inProgressCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->completedLessons > 0 && ! $dto->isCompleted)->count();

        $startedCourses = $coursesProgress->filter(fn (CourseProgressDTO $dto) => $dto->completedLessons > 0);
        $averageProgress = $startedCourses->count() > 0
            ? $startedCourses->avg('progressPercentage')
            : 0.0;

        return new UserStatisticsDTO(
            totalCompletedLessons: $totalCompletedLessons,
            totalCompletedCourses: $completedCourses,
            totalInProgressCourses: $inProgressCourses,
            averageProgress: (float) $averageProgress,
        );
    }

    private function createProgressDTO(?Course $course, int $totalLessons, int $completedLessons): CourseProgressDTO
    {
        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
        $isCompleted = $totalLessons > 0 && $completedLessons >= $totalLessons;
        $courseId = $course ? (int)$course->id : 0;
        $title = $course ? $course->title : 'Unknown Course';
        $previewImage = $course ? $course->preview_image : null;

        return new CourseProgressDTO(
            courseId: $courseId,
            title: $title,
            previewImageUrl: $previewImage,
            totalLessons: $totalLessons,
            completedLessons: $completedLessons,
            progressPercentage: round((float) $progress, 2),
            isCompleted: $isCompleted
        );
    }
}
