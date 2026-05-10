<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ToggleFavoriteDTO;
use App\DTO\ToggleLearnedDTO;
use App\Http\Requests\Education\ToggleFavoritePhraseRequest;
use App\Http\Requests\ToggleLearnedPhraseRequest;
use App\Http\Resources\FavouritePhrase\FavouritePhraseResource;
use App\Services\PhraseStateManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Handles HTTP operations for a user's favourite phrases.
 */
class FavoritePhraseController extends Controller
{
    /**
     * Constructs a new FavoritePhraseController instance.
     *
     * @param \App\Services\PhraseStateManager $phraseStateManager
     *
     * @return void
     */
    public function __construct(
        protected readonly PhraseStateManager $phraseStateManager,
    ) {
    }

    /**
     * Return all favourite phrases for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $favorites = auth()->user()
                ->favoritePhrases()
                ->with('phrase')
                ->get();

            return FavouritePhraseResource::collection($favorites)->response();
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve user favourite phrases', [
                'user_id' => auth()->id(),
                'action' => 'favorite_phrase_index_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to fetch favourite phrases',
            ], 500);
        }
    }

    /**
     * Toggle the favourite status of a phrase for the authenticated user.
     *
     * @param \App\Http\Requests\Education\ToggleFavoritePhraseRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(ToggleFavoritePhraseRequest $request): JsonResponse
    {
        $userId = auth()->id();
        $phraseId = $request->validated()['phrase_id'];

        try {
            $isFavorited = $this->phraseStateManager->toggleFavouriteState(
                new ToggleFavoriteDTO($phraseId, $userId),
            );

            return response()->json([
                'message' => $isFavorited ? 'Phrase added to favourites' : 'Phrase removed from favourites',
                'favorited' => $isFavorited,
            ]);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to toggle favourite phrase', [
                'user_id' => $userId,
                'phrase_id' => $phraseId,
                'action' => 'favorite_phrase_toggle_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to toggle favourite phrase',
            ], 500);
        }
    }

    /**
     * Toggle the learned state of an already-favourited phrase.
     *
     * @param \App\Http\Requests\ToggleLearnedPhraseRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLearned(ToggleLearnedPhraseRequest $request): JsonResponse
    {
        $userId = auth()->id();
        $phraseId = $request->validated()['phrase_id'];

        try {
            $isLearned = $this->phraseStateManager->toggleLearnedState(
                new ToggleLearnedDTO($phraseId, $userId),
            );

            return response()->json([
                'message' => $isLearned ? 'Phrase marked as learned' : 'Phrase marked as unlearned',
                'is_learned' => $isLearned,
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Phrase not found in your favourites',
            ], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to toggle phrase learned state', [
                'user_id' => $userId,
                'phrase_id' => $phraseId,
                'action' => 'favorite_phrase_learned_toggle_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to update phrase learned state',
            ], 500);
        }
    }
}
