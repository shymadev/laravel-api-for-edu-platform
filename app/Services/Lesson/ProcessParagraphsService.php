<?php

declare(strict_types=1);

namespace App\Services\Lesson;

use App\Services\Lesson\Enums\ParagraphType;
use Illuminate\Container\Attributes\Singleton;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Placeholder for processing lesson paragraph uploads (e.g. audio).
 */
#[Singleton]
class ProcessParagraphsService
{
    /**
     * Process uploaded files referenced by paragraph data.
     *
     * @param array<int, array<string, mixed>> $paragraphs
     * @param FileBag $files
     *
     * @return void
     */
    public function execute(array $paragraphs, FileBag $files): void
    {
        foreach ($paragraphs as $paragraph) {
            // @phpstan-ignore-next-line
            match ($paragraph['type']) {
                ParagraphType::AUDIO => $paragraph['audio'],
                default => null,
            };
        }
    }

    /**
     * Process a single audio-type paragraph.
     *
     * @param array<string, mixed> $paragraph
     *
     * @return void
     */
    protected function processAudioParagraph(array $paragraph): void
    {
    }
}
