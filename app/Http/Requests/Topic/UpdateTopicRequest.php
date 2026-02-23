<?php

declare(strict_types=1);

namespace App\Http\Requests\Topic;

use App\DTO\Topic\UpdateTopicDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for updating a topic.
 */
class UpdateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:topics,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.integer' => 'The course ID must be an integer.',
            'course_id.exists' => 'The selected course does not exist.',
            'parent_id.integer' => 'The parent topic ID must be an integer.',
            'parent_id.exists' => 'The selected parent topic does not exist.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
        ];
    }

    public function toDTO(): UpdateTopicDTO
    {
        $data = $this->validated();
        $data['id'] = (int) $this->route('topic')->id;

        return UpdateTopicDTO::fromArray($data);
    }
}
