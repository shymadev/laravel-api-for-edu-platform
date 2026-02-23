<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Education\ToggleFavoritePhraseRequest;
use App\Http\Resources\FavouritePhrase\FavouritePhraseResource;
use App\Models\Education\FavoritePhrase;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles favorite phrase operations.
 */
class FavoritePhraseController extends Controller
{
    /**
     * Display a listing of the user's favorite phrases.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $favorites = auth()->user()->favoritePhrases()->with('phrase')->get();

            return FavouritePhraseResource::collection($favorites)->response();
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve user favorite phrases', [
                'user_id' => auth()->id(),
                'action' => 'favorite_phrase_index_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to fetch favorite phrases',
            ], 500);
        }
    }

    /**
     * Toggle the favorite status of a phrase.
     *
     * @param ToggleFavoritePhraseRequest $request The validated request containing phrase ID
     *
     * @return JsonResponse
     */
    public function toggle(ToggleFavoritePhraseRequest $request): JsonResponse
    {
        $userId = auth()->id();
        $phraseId = $request->validated()['phrase_id'];

        try {
            $favorite = FavoritePhrase::where('user_id', $userId)
                ->where('phrase_id', $phraseId)
                ->first();

            if ($favorite !== null) {
                $favorite->deleteOrFail();

                Log::channel('db')->info('Favorite phrase removed', [
                    'user_id' => $userId,
                    'phrase_id' => $phraseId,
                    'action' => 'favorite_phrase_removed',
                ]);

                return response()->json([
                    'message' => 'Phrase removed from favorites',
                    'favorited' => false,
                ]);
            }

            FavoritePhrase::create([
                'user_id' => $userId,
                'phrase_id' => $phraseId,
            ]);

            Log::channel('db')->info('Favorite phrase added', [
                'user_id' => $userId,
                'phrase_id' => $phraseId,
                'action' => 'favorite_phrase_added',
            ]);

            return response()->json([
                'message' => 'Phrase added to favorites',
                'favorited' => true,
            ]);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to toggle favorite phrase', [
                'user_id' => $userId,
                'phrase_id' => $phraseId,
                'action' => 'favorite_phrase_toggle_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to toggle favorite phrase',
            ], 500);
        }
    }
}
