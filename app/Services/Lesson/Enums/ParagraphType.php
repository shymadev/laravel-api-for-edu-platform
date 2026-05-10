<?php

declare(strict_types=1);

namespace App\Services\Lesson\Enums;

enum ParagraphType: string
{
    case AUDIO = 'audio';
    case TEXT = 'text';
    case VIDEO = 'video';
    case VOCABULARY_GAME = 'vocabulary-game';
}
