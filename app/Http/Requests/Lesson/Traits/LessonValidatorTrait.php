<?php

declare(strict_types=1);

namespace App\Http\Requests\Lesson\Traits;

use App\Services\Lesson\LessonService;
use Illuminate\Validation\Validator;

trait LessonValidatorTrait
{
    /**
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $content = $this->input('content');
            $content = $content !== null ? json_decode($content, true) : null;

            if ($content !== null) {
                $lessonService = app(LessonService::class);
                $errors = $lessonService->validateContent($content);

                foreach ($errors as $error) {
                    $validator->errors()->add('content', $error);
                }
            }
        });
    }
}
