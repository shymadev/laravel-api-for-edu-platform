<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Entities\SearchOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Http\Controllers\Helpers\SearcherTrait;
use App\Http\Requests\Education\CreatePhraseRequest;
use App\Http\Requests\Education\UpdatePhraseRequest;
use App\Http\Resources\Education\PhraseResource;
use App\Models\Education\Phrase;
use App\Services\PhraseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles phrase-related operations.
 */
class PhraseController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    /**
     * Constructs a new PhraseController instance.
     *
     * @param \App\Services\PhraseService $phraseService
     *
     * @return void
     */
    public function __construct(
        protected readonly PhraseService $phraseService,
    ) {
    }

    /**
     * Display a listing of phrases with optional pagination and search.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);
        $category = $request->query('category');
        $difficultyLevel = $request->query('difficulty_level') !== null
            ? (int) $request->query('difficulty_level')
            : null;

        $sortBy = in_array($request->query('sort_by'), ['created_at', 'text'], true)
            ? $request->query('sort_by')
            : 'created_at';
        $sortDir = in_array(strtolower((string) $request->query('sort_dir', 'desc')), ['asc', 'desc'], true)
            ? strtolower((string) $request->query('sort_dir', 'desc'))
            : 'desc';

        $query = Phrase::query()->where('is_phrasebook', true);

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['text', 'translation']);
        }

        if ($category !== null && $category !== '') {
            $query->where('topic', $category);
        }

        if ($difficultyLevel !== null) {
            $query->join('difficulty_levels', 'phrases.difficulty_level_id', '=', 'difficulty_levels.id')
                ->where('difficulty_levels.id', $difficultyLevel);
        }

        $query->orderBy($sortBy === 'text' ? 'phrases.text' : 'phrases.created_at', $sortDir);

        return PhraseResource::collection(
            $paginationOptions instanceof PaginationOptions
                ? $this->paginateQuery($query, $paginationOptions)
                : $query->get(),
        );
    }

    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->phraseService->getAllCategories();

        return response()->json(['categories' => $categories]);
    }

    /**
     * Display the specified phrase.
     *
     * @param int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        try {
            $phrase = $this->phraseService->getPhraseById((int) $id);

            return response()->json(new PhraseResource($phrase));
        } catch (\Exception $e) {
            Log::channel('db')->error('Failed to retrieve phrase', [
                'phrase_id' => $id,
                'action' => 'phrase_show_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to retrieve phrase'], 500);
        }
    }

    /**
     * Store a new phrase.
     *
     * @param \App\Http\Requests\Education\CreatePhraseRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreatePhraseRequest $request): JsonResponse
    {
        try {
            $phrase = $this->phraseService->createPhrase($request->toDTO());

            Log::channel('db')->info('Phrase created', [
                'phrase_id' => $phrase->id,
                'action' => 'phrase_store',
            ]);

            return response()->json(new PhraseResource($phrase), 201);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to create phrase', [
                'action' => 'phrase_store_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to create phrase'], 500);
        }
    }

    /**
     * Update the specified phrase.
     *
     * @param \App\Http\Requests\Education\UpdatePhraseRequest $request
     * @param \App\Models\Education\Phrase $phrase
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePhraseRequest $request, Phrase $phrase): JsonResponse
    {
        try {
            $phrase = $this->phraseService->updatePhrase($phrase, $request->toDTO());

            Log::channel('db')->info('Phrase updated', [
                'phrase_id' => $phrase->id,
                'action' => 'phrase_update',
            ]);

            return PhraseResource::make($phrase)->response();

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to update phrase', [
                'phrase_id' => $phrase->id,
                'action' => 'phrase_update_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to update phrase'], 500);
        }
    }

    /**
     * Resolve a phrase by text and translation.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resolve(Request $request): JsonResponse
    {
        $text = (string) $request->input('text', '');
        $translation = (string) $request->input('translation', '');

        if ($text === '' || $translation === '') {
            return response()->json(['message' => 'text and translation are required'], 422);
        }

        try {
            $phrase = $this->phraseService->resolveByText($text, $translation);

            return response()->json(new PhraseResource($phrase));
        } catch (\Exception $e) {
            Log::channel('db')->error('Failed to resolve phrase', [
                'text' => $text,
                'action' => 'phrase_resolve_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to resolve phrase'], 500);
        }
    }

    /**
     * Delete the specified phrase.
     *
     * @param int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int|string $id): JsonResponse
    {
        try {
            $this->phraseService->deletePhrase((int) $id);

            Log::channel('db')->info('Phrase deleted', [
                'phrase_id' => $id,
                'action' => 'phrase_destroy',
            ]);

            return response()->json(null, 204);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to delete phrase', [
                'phrase_id' => $id,
                'action' => 'phrase_destroy_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to delete phrase'], 500);
        }
    }
}
