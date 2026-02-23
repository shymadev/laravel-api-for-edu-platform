<?php

declare(strict_types=1);

namespace App\Http\Resources\Education;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Education\Phrase
 */
class PhraseResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'translation' => $this->translation,
            'difficulty_level_id' => $this->difficulty_level_id,
            'difficulty_level' => $this->difficultyLevel ? [
                'id' => $this->difficultyLevel->id,
                'name' => $this->difficultyLevel->name,
                'value' => $this->difficultyLevel->value,
                'description' => $this->difficultyLevel->description,
            ] : null,
            'topic' => $this->topic,
            'audio_url' => $this->audio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
