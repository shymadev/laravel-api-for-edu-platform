<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request body for toggling a phrase's learned state.
 */
class ToggleLearnedPhraseRequest extends FormRequest
{
    /**
     * Only authenticated users may toggle the learned state of their phrases.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules for the toggle-learned request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phrase_id' => ['required', 'integer', 'exists:phrases,id'],
        ];
    }
}
