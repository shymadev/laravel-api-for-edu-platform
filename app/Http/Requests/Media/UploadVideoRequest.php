<?php

declare(strict_types=1);

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates video upload requests.
 */
class UploadVideoRequest extends FormRequest
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
            'video' => [
                'required',
                'file',
                'mimes:mp4,webm,ogg,mov,avi,mpeg',
                'max:512000', // 500 MB
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'video.required' => 'Файл видео обязателен.',
            'video.file' => 'Загрузите корректный файл.',
            'video.mimes' => 'Допустимые форматы: mp4, webm, ogg, mov, avi, mpeg.',
            'video.max' => 'Размер файла не должен превышать 500 МБ.',
        ];
    }
}
