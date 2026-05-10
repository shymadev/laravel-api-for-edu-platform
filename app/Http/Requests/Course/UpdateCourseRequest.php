<?php

declare(strict_types=1);

namespace App\Http\Requests\Course;

use App\DTO\Course\UpdateCourseDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for updating a course.
 */
class UpdateCourseRequest extends FormRequest
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
            'language' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'difficulty_level_id' => ['sometimes', 'nullable', 'integer', 'exists:difficulty_levels,id'],
            'preview_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_premium' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'language.string' => 'The language must be a valid string.',
            'language.max' => 'The language must not exceed 255 characters.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.string' => 'The description must be a valid string.',
            'difficulty_level_id.integer' => 'The difficulty level ID must be an integer.',
            'difficulty_level_id.exists' => 'The selected difficulty level does not exist.',
            'preview_image.image' => 'The preview image must be an image file.',
            'preview_image.mimes' => 'The preview image must be a file of type: jpeg, png, jpg, webp.',
            'preview_image.max' => 'The preview image must not exceed 2MB.',
        ];
    }

    /**
     * Convert validated data to DTO.
     *
     * @return \App\DTO\Course\UpdateCourseDTO
     */
    public function toDTO(): UpdateCourseDTO
    {
        $data = $this->validated();
        $data['id'] = (int) $this->route('course')->id;

        return UpdateCourseDTO::fromArray($data);
    }
}
