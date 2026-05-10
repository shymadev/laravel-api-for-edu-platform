<?php

declare(strict_types=1);

namespace App\Http\Requests\Education;

use App\DTO\Phrase\CreatePhraseDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates phrase creation payload (education API).
 */
class CreatePhraseRequest extends FormRequest
{
    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'translation' => ['nullable', 'string', 'max:255'],
            'difficulty_level_id' => ['nullable', 'integer', 'exists:difficulty_levels,id'],
            'topic' => ['nullable', 'string', 'max:255'],
            'audio' => ['nullable', 'string'],
        ];
    }

    /**
     * @return \App\DTO\Phrase\CreatePhraseDTO
     */
    public function toDTO(): CreatePhraseDTO
    {
        return CreatePhraseDTO::fromArray($this->validated());
    }
}
