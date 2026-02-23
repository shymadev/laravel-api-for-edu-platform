<?php

declare(strict_types=1);

namespace App\Http\Requests\Education;

use App\DTO\Phrase\UpdatePhraseDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhraseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string', 'max:255'],
            'translation' => ['nullable', 'string', 'max:255'],
            'difficulty_level_id' => ['nullable', 'integer', 'exists:difficulty_levels,id'],
            'topic' => ['nullable', 'string', 'max:255'],
            'audio' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): UpdatePhraseDTO
    {
        return UpdatePhraseDTO::fromArray($this->validated());
    }
}
