<?php

namespace App\Http\Resources\FavouritePhrase;

use App\Http\Resources\Education\PhraseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouritePhraseResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phrase_id' => $this->phrase_id,
            'user_id' => $this->user_id,
            'phrase' => PhraseResource::make($this->whenLoaded('phrase')),
            'added_at' => $this->created_at,
        ];
    }
}
