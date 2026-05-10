<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Course\CreateCourseDTO;
use App\DTO\Course\UpdateCourseDTO;
use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Entities\SearchOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Http\Controllers\Helpers\SearcherTrait;
use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\Course\CourseResource;
use App\Models\Education\Course;
use App\Services\CourseService;
use App\Services\Storage\ImageStorageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Course controller.
 */
class CourseController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    /**
     * Constructs a new CourseController instance.
     *
     * @param \App\Services\CourseService $courseService
     * @param \App\Services\Storage\ImageStorageService $imageStorage
     *
     * @return void
     */
    public function __construct(
        protected readonly CourseService $courseService,
        protected readonly ImageStorageService $imageStorage,
    ) {
    }

    /**
     * Get all courses.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);
        $difficulty = $request->query('difficulty');
        $isPremium = $request->query('is_premium');

        $query = Course::query()->with(['topics', 'difficultyLevel']);

        $showArchived = $request->boolean('show_archived', false);
        if (!$showArchived) {
            $query->where('is_archived', false);
        }

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['title', 'description']);
        }

        if ($difficulty !== null && $difficulty !== '' && $difficulty !== 'All') {
            $query->whereHas('difficultyLevel', function ($q) use ($difficulty): void {
                $q->where('name', $difficulty);
            });
        }

        if ($isPremium !== null && $isPremium !== '') {
            $query->where('is_premium', $isPremium);
        }

        $sortOrder = strtolower((string) $request->query('sort_order', 'desc'));
        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }
        $query->orderBy('created_at', $sortOrder);

        if ($paginationOptions instanceof PaginationOptions) {
            return CourseResource::collection(
                $this->paginateQuery($query, $paginationOptions),
            );
        }

        return CourseResource::collection($query->get());
    }

    /**
     * Create a new course.
     *
     * @param \App\Http\Requests\Course\CreateCourseRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateCourseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('preview_image')) {
                $path = $this->imageStorage->upload(
                    $request->file('preview_image'),
                    'course_images',
                );
                $data['preview_image'] = $path;
            }

            $dto = CreateCourseDTO::fromArray($data);
            $course = $this->courseService->createCourse($dto);

            Log::channel('db')->info('Course created', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_create',
            ]);

            return CourseResource::make($course)
                ->response()
                ->setStatusCode(201);

        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to create course', [
                'user_id' => auth()->id(),
                'action' => 'course_create_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to create course',
            ], 500);
        }
    }

    /**
     * Get a course by its ID.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Course $course): JsonResponse
    {
        try {
            $course = $this->courseService->getCourseById($course->id);

            return response()->json(CourseResource::make($course));
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Course not found'], 404);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve course',
            ], 500);
        }
    }

    /**
     * Update a course.
     *
     * @param \App\Http\Requests\Course\UpdateCourseRequest $request
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = $course->id;

            if ($request->hasFile('preview_image')) {
                $path = $this->imageStorage->upload(
                    $request->file('preview_image'),
                    'course_images',
                );
                $data['preview_image'] = $path;
            }

            $dto = UpdateCourseDTO::fromArray($data);
            $course = $this->courseService->updateCourse($course, $dto);

            Log::channel('db')->info('Course updated', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_update',
            ]);

            return CourseResource::make($course)->response();

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Course not found'], 404);
        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to update course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_update_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to update course',
            ], 500);
        }
    }

    /**
     * Delete a course.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Course $course): JsonResponse
    {
        try {
            $this->courseService->deleteCourse($course);

            Log::channel('db')->info('Course deleted', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_delete',
            ]);

            return response()->json(null, 204);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Course not found'], 404);
        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to delete course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_delete_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to delete course',
            ], 500);
        }
    }

    /**
     * Publish a course.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Course $course): JsonResponse
    {
        try {
            $course = $this->courseService->publish($course);

            Log::channel('db')->info('Course published', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_publish',
            ]);

            return CourseResource::make($course)->response();

        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to publish course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_publish_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to publish course',
            ], 500);
        }
    }

    /**
     * Unpublish a course.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unpublish(Course $course): JsonResponse
    {
        try {
            $course = $this->courseService->unpublish($course);

            Log::channel('db')->info('Course unpublished', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_unpublish',
            ]);

            return CourseResource::make($course)->response();

        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to unpublish course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_unpublish_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to unpublish course',
            ], 500);
        }
    }

    /**
     * Archive a course.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive(Course $course): JsonResponse
    {
        try {
            $course = $this->courseService->archive($course);

            Log::channel('db')->info('Course archived', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_archive',
            ]);

            return CourseResource::make($course)->response();

        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to archive course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_archive_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to archive course',
            ], 500);
        }
    }

    /**
     * Unarchive a course.
     *
     * @param \App\Models\Education\Course $course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unarchive(Course $course): JsonResponse
    {
        try {
            $course = $this->courseService->unarchive($course);

            Log::channel('db')->info('Course unarchived', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_unarchive',
            ]);

            return CourseResource::make($course)->response();

        } catch (Throwable $e) {
            Log::channel('db')->error('Failed to unarchive course', [
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'action' => 'course_unarchive_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to unarchive course',
            ], 500);
        }
    }
}
