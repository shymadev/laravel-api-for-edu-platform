<?php

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;

class CreateLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:0'],
            'content' => ['nullable', 'array'],
            'content.*.type' => ['required', 'string', 'in:video,text,phrases,test'],
            'content.*.order' => ['required', 'integer', 'min:0'],
        ];
    }
}
