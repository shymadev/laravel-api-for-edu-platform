<?php

declare(strict_types=1);

namespace App\Services\Contracts\Course;

use App\DTO\Course\CreateCourseDTO;
use App\DTO\Course\UpdateCourseDTO;
use App\Models\Education\Course;

/**
 * Interface for Course service operations.
 */
interface CourseServiceInterface
{
    /**
     * Get a course by its ID.
     *
     * @param int $id
     *
     * @return Course
     */
    public function getCourseById(int $id): Course;

    /**
     * Create a new course.
     *
     * @param CreateCourseDTO $dto
     *
     * @return Course
     */
    public function createCourse(CreateCourseDTO $dto): Course;

    /**
     * Update an existing course.
     *
     * @param Course          $course
     * @param UpdateCourseDTO $dto
     *
     * @return Course
     */
    public function updateCourse(Course $course, UpdateCourseDTO $dto): Course;

    /**
     * Delete a course.
     *
     * @param Course $course
     *
     * @return bool
     */
    public function deleteCourse(Course $course): bool;

    /**
     * Set "is_active" flag to TRUE.
     *
     * @param Course $course
     *
     * @return Course
     */
    public function publish(Course $course): Course;

    /**
     * Set "is_active" flag to FALSE.
     *
     * @param Course $course
     *
     * @return Course
     */
    public function unpublish(Course $course): Course;
}
