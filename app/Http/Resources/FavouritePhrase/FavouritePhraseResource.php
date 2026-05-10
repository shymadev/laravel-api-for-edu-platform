<?php

declare(strict_types=1);

namespace App\Http\Resources\FavouritePhrase;

use App\Http\Resources\Education\PhraseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Education\FavoritePhrase
 */
class FavouritePhraseResource extends JsonResource
{
    /**
     * Transform the FavoritePhrase model into a JSON-serialisable array.
     *
     * The `is_learned` flag is included so the frontend practice module can
     * filter already-learned phrases and allow users to reset them.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phrase_id' => $this->phrase_id,
            'user_id' => $this->user_id,
            'is_learned' => $this->is_learned,
            'phrase' => PhraseResource::make($this->whenLoaded('phrase')),
            'added_at' => $this->added_at,
        ];
    }
}
