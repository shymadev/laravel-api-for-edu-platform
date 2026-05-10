<?php

declare(strict_types=1);

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates block progress save payload (education API).
 */
class SaveBlockProgressRequest extends FormRequest
{
    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'block_index' => ['required', 'integer', 'min:0'],
            'block_type' => ['required', 'string', 'max:50'],
            'is_completed' => ['sometimes', 'boolean'],
            'block_state' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
