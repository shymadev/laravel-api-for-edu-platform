<?php

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoritePhraseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phrase_id' => ['required', 'integer', 'exists:phrases,id'],
        ];
    }
}
