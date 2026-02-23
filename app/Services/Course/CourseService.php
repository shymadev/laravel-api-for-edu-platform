<?php

declare(strict_types=1);

namespace App\Services\Course;

use App\DTO\Course\CreateCourseDTO;
use App\DTO\Course\UpdateCourseDTO;
use App\Models\Education\Course;
use App\Services\Contracts\Course\CourseServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

/**
 * Service for handling course-related business logic.
 */
class CourseService implements CourseServiceInterface
{
    /**
     * Get a course by its ID.
     *
     * @param int $id
     *
     * @throws ModelNotFoundException
     *
     * @return Course
     */
    public function getCourseById(int $id): Course
    {
        return Course::with(['topics.lessons'])->findOrFail($id);
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
        return Course::create($dto->toArray());
    }

    /**
     * Update an existing course.
     *
     * @param Course          $course
     * @param UpdateCourseDTO $dto
     *
     * @return Course
     */
    public function updateCourse(Course $course, UpdateCourseDTO $dto): Course
    {
        $data = $dto->toArray();
        unset($data['id']);

        if (isset($data['preview_image']) && $data['preview_image'] !== null && $course->preview_image) {
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
     * @return bool
     */
    public function deleteCourse(Course $course): bool
    {
        if ($course->preview_image) {
            $this->deletePreviewImage($course->preview_image);
        }

        return (bool) $course->delete();
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

    /**
     * {@inheritdoc}
     */
    public function publish(Course $course): Course
    {
        $course->update(['is_active' => true]);
        $course->save();

        return $course->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(Course $course): Course
    {
        $course->update(['is_active' => false]);
        $course->save();

        return $course->refresh();
    }
}
