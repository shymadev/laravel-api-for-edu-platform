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
use App\Services\Contracts\Course\CourseServiceInterface;
use App\Services\Storage\ImageStorageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Throwable;

class CourseController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    public function __construct(
        protected readonly CourseServiceInterface $courseService,
        protected readonly ImageStorageService $imageStorage,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);
        $difficulty = $request->query('difficulty');

        $query = Course::query()->with(['topics', 'difficultyLevel']);

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['title', 'description']);
        }

        if ($difficulty && $difficulty !== 'All') {
            $query->whereHas('difficultyLevel', function ($q) use ($difficulty) {
                $q->where('name', $difficulty);
            });
        }

        if ($paginationOptions instanceof PaginationOptions) {
            return CourseResource::collection(
                $this->paginateQuery($query, $paginationOptions)
            );
        }

        return CourseResource::collection($query->get());
    }

    public function store(CreateCourseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('preview_image')) {
                $path = $this->imageStorage->upload(
                    $request->file('preview_image'),
                    'course_images'
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

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = (int) $course->id;

            if ($request->hasFile('preview_image')) {
                $path = $this->imageStorage->upload(
                    $request->file('preview_image'),
                    'course_images'
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
}
