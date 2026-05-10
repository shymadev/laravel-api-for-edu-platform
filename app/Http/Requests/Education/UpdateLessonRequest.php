<?php

declare(strict_types=1);

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates lesson update payload (education API).
 */
class UpdateLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic_id' => ['sometimes', 'integer', 'exists:topics,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'weight' => ['sometimes', 'integer', 'min:0'],
            'content' => ['sometimes', 'array'],
            'content.*.type' => ['required', 'string', 'in:video,text,phrases,test'],
            'content.*.order' => ['required', 'integer', 'min:0'],
        ];
    }
}
