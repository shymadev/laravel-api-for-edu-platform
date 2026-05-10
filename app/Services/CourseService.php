<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Course\CreateCourseDTO;
use App\DTO\Course\UpdateCourseDTO;
use App\Models\Education\Course;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Service for handling course-related business logic.
 */
#[Singleton]
class CourseService
{
    /**
     * Get a course by its ID.
     *
     * @param int $id
     *
     * @return Course
     *
     * @throws ModelNotFoundException
     */
    public function getCourseById(int $id): Course
    {
        return Cache::tags(['courses', "course.{$id}"])->remember(
            "course.{$id}",
            3600,
            fn () => Course::with(['topics.lessons'])->findOrFail($id),
        );
    }

    /**
     * Create a new course.
     *
     * @param CreateCourseDTO $dto
     *
     * @return Course
     */
    public function createCourse(CreateCourseDTO $dto): Course
    {
        $course = Course::create($dto->toArray());
        $course->created_at = now();
        $course->is_active = false;
        $course->save();

        return $course;
    }

    /**
     * Update an existing course.
     *
     * @param Course $course
     * @param UpdateCourseDTO $dto
     *
     * @return Course
     */
    public function updateCourse(Course $course, UpdateCourseDTO $dto): Course
    {
        $data = $dto->toArray();
        unset($data['id']);

        if (isset($data['preview_image']) && $course->preview_image !== null) {
            $this->deletePreviewImage($course->preview_image);
        }

        $course->update($data);
        $course->refresh();

        return $course;
    }

    /**
     * Delete a course.
     *
     * @param Course $course
     *
     * @return boolean
     */
    public function deleteCourse(Course $course): bool
    {
        if ($course->preview_image !== null) {
            $this->deletePreviewImage($course->preview_image);
        }

        return (bool) $course->delete();
    }

    /**
     * @param Course $course
     *
     * @return Course
     */
    public function publish(Course $course): Course
    {
        $course->update(['is_active' => true]);
        $course->save();

        return $course->refresh();
    }

    /**
     * @param Course $course
     *
     * @return Course
     */
    public function unpublish(Course $course): Course
    {
        $course->update(['is_active' => false]);
        $course->save();

        return $course->refresh();
    }

    /**
     * @param Course $course
     *
     * @return Course
     */
    public function archive(Course $course): Course
    {
        $course->update(['is_archived' => true, 'is_active' => false]);

        return $course->refresh();
    }

    /**
     * @param Course $course
     *
     * @return Course
     */
    public function unarchive(Course $course): Course
    {
        $course->update(['is_archived' => false]);

        return $course->refresh();
    }

    /**
     * Delete a preview image from storage.
     *
     * @param string $path
     *
     * @return void
     */
    private function deletePreviewImage(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
