<?php

namespace App\Services\Lesson\Enums;

enum ParagraphType: string
{
    case AUDIO = 'audio';
    case TEXT = 'text';
    case VIDEO = 'video';
    case VOCABULARY_GAME = 'vocabulary-game';
}
