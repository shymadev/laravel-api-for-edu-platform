<?php

declare(strict_types=1);

namespace App\Http\Requests\Lesson;

use App\DTO\Lesson\UpdateLessonDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates lesson update for the admin API.
 */
class UpdateLessonRequest extends FormRequest
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
            'topic_id' => ['sometimes', 'nullable', 'integer', 'exists:topics,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'weight' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'content' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'topic_id.exists' => 'The selected topic does not exist.',
            'title.string' => 'The title must be a valid string.',
            'weight.integer' => 'The weight must be an integer.',
            'weight.min' => 'The weight must be at least 0.',
        ];
    }

    /**
     * @return \App\DTO\Lesson\UpdateLessonDTO
     */
    public function toDTO(): UpdateLessonDTO
    {
        $data = $this->validated();
        $data['content'] = isset($data['content']) ? json_decode($data['content'], true) : null;
        $data['id'] = (int) $this->route('lesson')->id;
        $data['files'] = $this->files->getIterator();

        return UpdateLessonDTO::fromArray($data);
    }
}
