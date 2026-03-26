<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Container\Attributes\Singleton;
use App\Models\Education\FavoritePhrase;
use Illuminate\Support\Facades\Log;
use App\DTO\ToggleFavoriteDTO;
use App\DTO\ToggleLearnedDTO;

/**
 * Manages the mutable state of a user's favourite phrases.
 */
#[Singleton]
class PhraseStateManager
{

    /**
     * Toggle the favourite state of a phrase for a given user.
     *
     * @param  ToggleFavoriteDTO $dto  Phrase and user identifiers.
     * 
     * @return bool True if the phrase is now favourited, false if it was just removed.      
     *
     * @throws \Throwable
     */
    public function toggleFavouriteState(ToggleFavoriteDTO $dto): bool
    {
        try {
            $favorite = FavoritePhrase::where('user_id', $dto->userId)
                ->where('phrase_id', $dto->phraseId)
                ->first();

            if ($favorite !== null) {
                $this->removeFavorite($favorite);
                return false;
            }

            $this->addFavorite($dto->phraseId, $dto->userId);
            return true;
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to toggle favourite phrase', [
                'user_id'   => $dto->userId,
                'phrase_id' => $dto->phraseId,
                'action'    => 'favorite_phrase_toggle_failed',
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Toggle the `is_learned` flag on an existing favourite phrase.
     *
     * @param  ToggleLearnedDTO $dto  Phrase and user identifiers.
     * 
     * @return bool True if the phrase is now learned, false if it was just unlearned.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function toggleLearnedState(ToggleLearnedDTO $dto): bool
    {
        try {
            $favorite = FavoritePhrase::where('user_id', $dto->userId)
                ->where('phrase_id', $dto->phraseId)
                ->firstOrFail();

            $newState = !$favorite->is_learned;

            $favorite->update(['is_learned' => $newState]);

            Log::channel('db')->info('Phrase learned state toggled', [
                'user_id'    => $dto->userId,
                'phrase_id'  => $dto->phraseId,
                'is_learned' => $newState,
                'action'     => 'favorite_phrase_learned_toggled',
            ]);

            return $newState;
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to toggle phrase learned state', [
                'user_id'   => $dto->userId,
                'phrase_id' => $dto->phraseId,
                'action'    => 'favorite_phrase_learned_toggle_failed',
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Remove a favourite phrase record from the database.
     *
     * @param  FavoritePhrase $favorite  The record to delete.
     * @return void
     *
     * @throws \Throwable
     */
    protected function removeFavorite(FavoritePhrase $favorite): void
    {
        $favorite->deleteOrFail();

        Log::channel('db')->info('Favourite phrase removed', [
            'user_id'   => $favorite->user_id,
            'phrase_id' => $favorite->phrase_id,
            'action'    => 'favorite_phrase_removed',
        ]);
    }

    /**
     * Create a new favourite phrase record for the given user.
     *
     * @param  int $phraseId  The ID of the phrase to favourite.
     * @param  int $userId    The ID of the user who is favouriting the phrase.
     * 
     * @return void
     *
     * @throws \Throwable
     */
    protected function addFavorite(int $phraseId, int $userId): void
    {
        FavoritePhrase::create([
            'user_id'    => $userId,
            'phrase_id'  => $phraseId,
            'is_learned' => false,
        ]);

        Log::channel('db')->info('Favourite phrase added', [
            'user_id'   => $userId,
            'phrase_id' => $phraseId,
            'action'    => 'favorite_phrase_added',
        ]);
    }

}
