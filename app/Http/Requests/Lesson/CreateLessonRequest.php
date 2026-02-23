<?php

declare(strict_types=1);

namespace App\Http\Requests\Lesson;

use App\DTO\Lesson\CreateLessonDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateLessonRequest extends FormRequest
{
    use Traits\LessonValidatorTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define the validation rules for creating a lesson.
     */
    public function rules(): array
    {
        return [
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:0'],
            'content' => ['required', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function messages(): array
    {
        return [
            'topic_id.required' => 'The topic ID is required.',
            'topic_id.exists' => 'The selected topic does not exist.',
            'title.required' => 'The lesson title is required.',
            'weight.required' => 'The lesson weight is required.',
            'weight.min' => 'The weight must be at least 0.',
            'content.required' => 'The lesson content is required.',
        ];
    }

    /**
     * Convert the request data to a CreateLessonDTO.
     */
    public function toDTO(): CreateLessonDTO
    {
        $data = $this->validated();
        $data['content'] = isset($data['content']) ? json_decode($data['content'], true) : null;
        $data['files'] = $this->files->getIterator();

        return CreateLessonDTO::fromArray($data);
    }
}
