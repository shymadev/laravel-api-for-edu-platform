<?php

declare(strict_types=1);

namespace App\Services\Education;

use App\Models\Education\Paragraphs\AudioParagraph;
use App\Models\Education\Paragraphs\BaseParagraph;
use App\Models\Education\Paragraphs\CategorizationParagraph;
use App\Models\Education\Paragraphs\FillGapsParagraph;
use App\Models\Education\Paragraphs\MatchingParagraph;
use App\Models\Education\Paragraphs\PhraseParagraph;
use App\Models\Education\Paragraphs\PreListeningParagraph;
use App\Models\Education\Paragraphs\SentenceTaskParagraph;
use App\Models\Education\Paragraphs\TestParagraph;
use App\Models\Education\Paragraphs\TextParagraph;
use App\Models\Education\Paragraphs\TranslationParagraph;
use App\Models\Education\Paragraphs\VideoParagraph;
use App\Models\Education\Paragraphs\VocabularyGameParagraph;

class ParagraphParser
{
    /**
     * Parse JSON content into paragraph objects.
     *
     * @param array $content
     *
     * @return BaseParagraph[]
     */
    public static function parse(array $content): array
    {
        return array_map(function (array $paragraphData) {
            return self::parseSingle($paragraphData);
        }, $content);
    }

    /**
     * Parse a single paragraph based on type.
     *
     * @param array $data
     *
     * @throws \InvalidArgumentException
     *
     * @return BaseParagraph
     */
    private static function parseSingle(array $data): BaseParagraph
    {
        $type = $data['type'] ?? null;

        return match ($type) {
            'video' => VideoParagraph::fromArray($data),
            'text' => TextParagraph::fromArray($data),
            'phrases' => PhraseParagraph::fromArray($data),
            'test' => TestParagraph::fromArray($data),
            'audio' => AudioParagraph::fromArray($data),
            'translation' => TranslationParagraph::fromArray($data),
            'matching' => MatchingParagraph::fromArray($data),
            'fill-gaps' => FillGapsParagraph::fromArray($data),
            'categorization' => CategorizationParagraph::fromArray($data),
            'sentence-task' => SentenceTaskParagraph::fromArray($data),
            'pre-listening' => PreListeningParagraph::fromArray($data),
            'vocabulary-game' => VocabularyGameParagraph::fromArray($data),

            default => throw new \InvalidArgumentException("Unknown paragraph type: {$type}"),
        };
    }

    /**
     * Serialize paragraphs to array for JSON storage.
     *
     * @param BaseParagraph[] $paragraphs
     *
     * @return array
     */
    public static function serialize(array $paragraphs): array
    {
        return array_map(fn (BaseParagraph $paragraph) => $paragraph->toArray(), $paragraphs);
    }
}
