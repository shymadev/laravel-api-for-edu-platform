<?php

declare(strict_types=1);

namespace App\Services\Contracts\Lesson;

use Symfony\Component\HttpFoundation\FileBag;

interface ProcessParagraphsInterface
{
    /**
     * Process an array of paragraphs.
     *
     * @param array   $paragraphs
     * @param FileBag $files
     *
     * @return void
     */
    public function execute(array $paragraphs, FileBag $files): void;
}
