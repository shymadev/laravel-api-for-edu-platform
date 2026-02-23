<?php

declare(strict_types=1);

namespace App\Http\Requests\Topic;

use App\DTO\Topic\CreateTopicDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for creating a new topic.
 */
class CreateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'parent_id' => ['nullable', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'The course ID is required.',
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'parent_id.integer' => 'The parent topic ID must be an integer.',
            'parent_id.exists' => 'The selected parent topic does not exist.',
            'title.required' => 'The topic title is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
        ];
    }

    public function toDTO(): CreateTopicDTO
    {
        return CreateTopicDTO::fromArray($this->validated());
    }
}
