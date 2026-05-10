<?php

declare(strict_types=1);

namespace App\Http\Requests\Course;

use App\DTO\Course\CreateCourseDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for creating a new course.
 */
class CreateCourseRequest extends FormRequest
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
            'language' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'difficulty_level_id' => ['nullable', 'integer', 'exists:difficulty_levels,id'],
            'preview_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_premium' => ['nullable', 'string', 'in:true,false,1,0'],
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
            'language.required' => 'The language field is required.',
            'language.string' => 'The language must be a valid string.',
            'language.max' => 'The language must not exceed 255 characters.',
            'title.required' => 'The course title is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.string' => 'The description must be a valid string.',
            'difficulty_level_id.integer' => 'The difficulty level ID must be an integer.',
            'difficulty_level_id.exists' => 'The selected difficulty level does not exist.',
            'preview_image.image' => 'The preview image must be an image file.',
            'preview_image.mimes' => 'The preview image must be a file of type: jpeg, png, jpg, webp.',
            'preview_image.max' => 'The preview image must not exceed 2MB.',
            'is_premium.in' => 'The is_premium field must be true, false, 1, or 0.',
        ];
    }

    /**
     * Convert validated data to DTO.
     *
     * @return \App\DTO\Course\CreateCourseDTO
     */
    public function toDTO(): CreateCourseDTO
    {
        return CreateCourseDTO::fromArray($this->validated());
    }
}
