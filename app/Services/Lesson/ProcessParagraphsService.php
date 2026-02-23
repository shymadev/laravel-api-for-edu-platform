<?php

declare(strict_types=1);

namespace App\Services\Lesson;

use App\Services\Contracts\Lesson\ProcessParagraphsInterface;
use App\Services\Lesson\Enums\ParagraphType;
use Symfony\Component\HttpFoundation\FileBag;

class ProcessParagraphsService implements ProcessParagraphsInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $paragraphs, FileBag $files): void
    {
        foreach ($paragraphs as $paragraph) {
            match ($paragraph['type']) {
                ParagraphType::AUDIO => $paragraph['audio'],
                default => null,
            };
        }
    }

    protected function processAudioParagraph(array $paragraph): void
    {

    }
}
