<?php

declare(strict_types=1);

namespace App\Http\Requests\Lesson\Traits;

use App\Services\Contracts\Lesson\LessonServiceInterface;

trait LessonValidatorTrait
{
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $content = $this->input('content');
            $content = $content !== null ? json_decode($content, true) : null;

            if ($content !== null) {
                $lessonService = app(LessonServiceInterface::class);
                $errors = $lessonService->validateContent($content);

                foreach ($errors as $error) {
                    $validator->errors()->add('content', $error);
                }
            }
        });
    }
}
