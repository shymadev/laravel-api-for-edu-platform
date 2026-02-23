<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Entities\SearchOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Http\Controllers\Helpers\SearcherTrait;
use App\Http\Requests\Lesson\CreateLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Contracts\Lesson\LessonServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    public function __construct(protected readonly LessonServiceInterface $lessonService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);

        $query = Lesson::query()->with(['topic']);

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['title']);
        }

        return LessonResource::collection(
            $paginationOptions instanceof PaginationOptions
                ? $this->paginateQuery($query, $paginationOptions)
                : $query->get()
        );
    }

    public function store(CreateLessonRequest $request): JsonResponse
    {
        try {
            $lesson = $this->lessonService->createLesson($request->toDTO(), $request->files);

            Log::channel('db')->info('Lesson created', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_store',
            ]);

            return LessonResource::make($lesson)->response()->setStatusCode(201);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to create lesson', [
                'action' => 'lesson_store_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to create lesson'], 500);
        }
    }

    public function show(int $lesson): JsonResponse
    {
        try {
            $lesson = $this->lessonService->getLessonById($lesson);

            return response()->json(LessonResource::make($lesson));
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Lesson not found'], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve lesson', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_show_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to retrieve lesson'], 500);
        }
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): JsonResponse
    {
        try {
            $lesson = $this->lessonService->updateLesson($lesson, $request->toDTO());

            Log::channel('db')->info('Lesson updated', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_update',
            ]);

            return LessonResource::make($lesson)->response();
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Lesson not found'], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to update lesson', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_update_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to update lesson'], 500);
        }
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        try {
            $deleted = $this->lessonService->deleteLesson($lesson);

            if (! $deleted) {
                return response()->json(['message' => 'Failed to delete lesson'], 400);
            }

            Log::channel('db')->info('Lesson deleted', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_destroy',
            ]);

            return response()->json(null, 204);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Lesson not found'], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to delete lesson', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_destroy_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to delete lesson'], 500);
        }
    }

    public function publish(Lesson $lesson): JsonResponse
    {
        try {
            $publishedLesson = $this->lessonService->publish($lesson);

            Log::channel('db')->info('Lesson published', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_publish',
            ]);

            return LessonResource::make($publishedLesson)->response();
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to publish lesson', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_publish_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to publish lesson'], 500);
        }
    }

    public function unpublish(Lesson $lesson): JsonResponse
    {
        try {
            $unpublishedLesson = $this->lessonService->unpublish($lesson);

            Log::channel('db')->info('Lesson unpublished', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_unpublish',
            ]);

            return LessonResource::make($unpublishedLesson)->response();
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to unpublish lesson', [
                'lesson_id' => $lesson->id,
                'action' => 'lesson_unpublish_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to unpublish lesson'], 500);
        }
    }

    public function getLessonsByTopic(Topic $topic): JsonResponse
    {
        try {
            $lessons = $this->lessonService->getLessonsByTopic($topic);

            return $lessons->response();
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Topic not found'], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve lessons by topic', [
                'topic_id' => $topic->id,
                'action' => 'lesson_get_by_topic_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to retrieve lessons'], 500);
        }
    }
}
