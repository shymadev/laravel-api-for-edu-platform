<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Entities\SearchOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Http\Controllers\Helpers\SearcherTrait;
use App\Http\Requests\Topic\CreateTopicRequest;
use App\Http\Requests\Topic\UpdateTopicRequest;
use App\Http\Resources\Topic\TopicResource;
use App\Models\Education\Course;
use App\Models\Education\Topic;
use App\Services\Contracts\Topic\TopicServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles topic-related operations.
 */
class TopicController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    /**
     * Topic service instance.
     *
     * @var TopicServiceInterface
     */
    protected readonly TopicServiceInterface $topicService;

    /**
     * Construct a new TopicController instance.
     *
     * @param TopicServiceInterface $topicService
     */
    public function __construct(TopicServiceInterface $topicService)
    {
        $this->topicService = $topicService;
    }

    /**
     * Display a listing of topics with optional pagination and search.
     *
     * @param Request $request
     *
     * @return AnonymousResourceCollection returns a collection of topics
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);

        $query = Topic::query()->with(['lessons']);

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['title']);
        }

        if ($paginationOptions instanceof PaginationOptions) {
            $topics = $this->paginateQuery($query, $paginationOptions);

            return TopicResource::collection($topics);
        }

        return TopicResource::collection($query->get());
    }

    /**
     * Store a newly created topic in storage.
     *
     * @param CreateTopicRequest $request
     *
     * @return JsonResponse returns the created topic or error message
     */
    public function store(CreateTopicRequest $request): JsonResponse
    {
        try {
            $topic = $this->topicService->createTopic($request->toDTO());
            Log::info('Topic created', ['topic_id' => $topic->id]);

            return TopicResource::make($topic)->response()->setStatusCode(201);
        } catch (\Exception $e) {
            Log::error('Failed to create topic', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to create topic',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified topic.
     *
     * @param Topic $topic
     *
     * @return JsonResponse returns topic data or error message
     */
    public function show(Topic $topic): JsonResponse
    {
        try {
            return response()->json(TopicResource::make($topic));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Topic not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve topic',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified topic in storage.
     *
     * @param UpdateTopicRequest $request
     * @param Topic              $topic
     *
     * @return JsonResponse returns updated topic or error message
     */
    public function update(UpdateTopicRequest $request, Topic $topic): JsonResponse
    {
        try {
            $topic = $this->topicService->updateTopic($topic, $request->toDTO());
            Log::info('Topic updated', ['topic_id' => $topic->id]);

            return TopicResource::make($topic)->response();
        } catch (ModelNotFoundException $e) {
            Log::warning('Topic not found for update', ['topic_id' => $topic->id]);

            return response()->json(['message' => 'Topic not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update topic', ['topic_id' => $topic->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to update topic',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified topic from storage.
     *
     * @param Topic $topic
     *
     * @return JsonResponse returns 204 on success or error message
     */
    public function destroy(Topic $topic): JsonResponse
    {
        try {
            $deleted = $this->topicService->deleteTopic($topic);

            if (! $deleted) {
                Log::warning('Failed to delete topic', ['topic_id' => $topic->id]);

                return response()->json(['message' => 'Failed to delete topic'], 400);
            }

            Log::info('Topic deleted', ['topic_id' => $topic->id]);

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            Log::warning('Topic not found for deletion', ['topic_id' => $topic->id]);

            return response()->json(['message' => 'Topic not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete topic', ['topic_id' => $topic->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete topic',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all topics for a specific course with optional pagination and search.
     *
     * @param Request $request
     * @param Course  $course
     *
     * @return AnonymousResourceCollection returns collection of topics
     */
    public function getTopicsByCourseId(Request $request, Course $course): AnonymousResourceCollection
    {
        try {
            $paginationOptions = $this->extractPaginationOptions($request);
            $searchOptions = $this->extractSearchOptions($request);

            $query = Topic::query()
                ->with(['lessons'])
                ->where('course_id', $course->id);

            if ($searchOptions instanceof SearchOptions) {
                $query = $this->addSearchConditions($query, $searchOptions, ['title']);
            }

            if ($paginationOptions instanceof PaginationOptions) {
                $topics = $this->paginateQuery($query->orderBy('title'), $paginationOptions);

                return TopicResource::collection($topics);
            }

            return TopicResource::collection($query->orderBy('title')->get());
        } catch (\Exception $e) {
            Log::error('Failed to retrieve topics for course', ['course_id' => $course->id, 'error' => $e->getMessage()]);

            return TopicResource::collection(collect());
        }
    }

    /**
     * Publish the specified topic.
     *
     * @param Topic $topic
     *
     * @return JsonResponse returns published topic or error message
     */
    public function publish(Topic $topic): JsonResponse
    {
        try {
            $publishedTopic = $this->topicService->publish($topic);
            Log::info('Topic published', ['topic_id' => $topic->id]);

            return TopicResource::make($publishedTopic)->response();
        } catch (\Exception $e) {
            Log::error('Failed to publish topic', ['topic_id' => $topic->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to publish topic',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Unpublish the specified topic.
     *
     * @param Topic $topic
     *
     * @return JsonResponse returns unpublished topic or error message
     */
    public function unpublish(Topic $topic): JsonResponse
    {
        try {
            $unpublishedTopic = $this->topicService->unpublish($topic);
            Log::info('Topic unpublished', ['topic_id' => $topic->id]);

            return TopicResource::make($unpublishedTopic)->response();
        } catch (\Exception $e) {
            Log::error('Failed to unpublish topic', ['topic_id' => $topic->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to unpublish topic',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
