<?php

declare(strict_types=1);

namespace App\Services\Lesson\Traits;

trait ParagraphValidatorTrait
{
    /**
     * Validate a single paragraph based on its type.
     *
     * @param string $type
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateParagraphByType(string $type, array $paragraph, int $index): array
    {
        return match ($type) {
            'text' => $this->validateTextParagraph($paragraph, $index),
            'video' => $this->validateVideoParagraph($paragraph, $index),
            'audio' => $this->validateAudioParagraph($paragraph, $index),
            'phrases' => $this->validatePhrasesParagraph($paragraph, $index),
            'test' => $this->validateTestParagraph($paragraph, $index),
            'translation' => $this->validateTranslationParagraph($paragraph, $index),
            'matching' => $this->validateMatchingParagraph($paragraph, $index),
            'fill-gaps' => $this->validateFillGapsParagraph($paragraph, $index),
            'categorization' => $this->validateCategorizationParagraph($paragraph, $index),
            'sentence-task' => $this->validateSentenceTaskParagraph($paragraph, $index),
            'pre-listening' => $this->validatePreListeningParagraph($paragraph, $index),
            'vocabulary-game' => $this->validateVocabularyGameParagraph($paragraph, $index),
            'speech-recognition' => $this->validateSpeechRecognitionParagraph($paragraph, $index),
            default => ["Unknown paragraph type '{$type}' at index {$index}"],
        };
    }

    /**
     * Validate a speech-recognition paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateSpeechRecognitionParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['content']) || $paragraph['content'] === '' || $paragraph['content'] === []) {
            return ["Speech-recognition paragraph at index {$index} must have non-empty 'content'"];
        }

        return [];
    }

    /**
     * Validate a text paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateTextParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['content']) || $paragraph['content'] === '' || $paragraph['content'] === []) {
            return ["Text paragraph at index {$index} must have non-empty 'content'"];
        }

        return [];
    }

    /**
     * Validate a video paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateVideoParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['url']) || $paragraph['url'] === '') {
            return ["Video paragraph at index {$index} must have non-empty 'url'"];
        }

        return [];
    }

    /**
     * Validate an audio paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateAudioParagraph(array $paragraph, int $index): array
    {
        $paragraphErrors = [];

        if (!isset($paragraph['content'])) {
            $paragraphErrors[] = "Audio paragraph at index {$index} must have 'content' field";
        } else {
            if ($paragraph['content']['type'] === 'stored_audio' && !isset($paragraph['content']['file_key'])) {
                $paragraphErrors[] = "Audio paragraph at index {$index} with type 'stored_audio' must have 'file_key'";
            }
        }

        return $paragraphErrors;
    }

    /**
     * Validate a phrases paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validatePhrasesParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['phrases']) || !is_array($paragraph['phrases']) || $paragraph['phrases'] === []) {
            return ["Phrases paragraph at index {$index} must have non-empty 'phrases' array"];
        }

        return [];
    }

    /**
     * Validate a test paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateTestParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['questions']) || !is_array($paragraph['questions']) || $paragraph['questions'] === []) {
            return ["Test paragraph at index {$index} must have non-empty 'questions' array"];
        }

        return [];
    }

    /**
     * Validate a translation paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateTranslationParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['pairs']) || !is_array($paragraph['pairs']) || $paragraph['pairs'] === []) {
            return ["Translation paragraph at index {$index} must have non-empty 'pairs' array"];
        }

        return [];
    }

    /**
     * Validate a translation paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateMatchingParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['pairs']) || !is_array($paragraph['pairs']) || $paragraph['pairs'] === []) {
            return ["Matching paragraph at index {$index} must have non-empty 'pairs' array"];
        }

        return [];
    }

    /**
     * Validate a fill-gaps paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateFillGapsParagraph(array $paragraph, int $index): array
    {
        $errors = [];

        if (!isset($paragraph['text']) || $paragraph['text'] === '') {
            $errors[] = "Fill-gaps paragraph at index {$index} must have non-empty 'text'";
        }

        if (!isset($paragraph['gaps']) || !is_array($paragraph['gaps'])) {
            $errors[] = "Fill-gaps paragraph at index {$index} must have 'gaps' array";
        }

        return $errors;
    }

    /**
     * Validate a categorization paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateCategorizationParagraph(array $paragraph, int $index): array
    {
        $errors = [];

        if (!isset($paragraph['categories']) || !is_array($paragraph['categories']) || $paragraph['categories'] === []) {
            $errors[] = "Categorization paragraph at index {$index} must have non-empty 'categories' array";
        }

        if (!isset($paragraph['items']) || !is_array($paragraph['items']) || $paragraph['items'] === []) {
            $errors[] = "Categorization paragraph at index {$index} must have non-empty 'items' array";
        }

        return $errors;
    }

    /**
     * Validate a sentence-task paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateSentenceTaskParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['tasks']) || !is_array($paragraph['tasks']) || $paragraph['tasks'] === []) {
            return ["Sentence-task paragraph at index {$index} must have non-empty 'tasks' array"];
        }

        return [];
    }

    /**
     * Validate a pre-listening paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validatePreListeningParagraph(array $paragraph, int $index): array
    {
        $errors = [];

        if (!isset($paragraph['taskType']) || $paragraph['taskType'] === '') {
            $errors[] = "Pre-listening paragraph at index {$index} must have 'taskType'";
        }

        if (!isset($paragraph['title']) || $paragraph['title'] === '') {
            $errors[] = "Pre-listening paragraph at index {$index} must have 'title'";
        }

        return $errors;
    }

    /**
     * Validate a vocabulary-game paragraph.
     *
     * @param array $paragraph
     * @param int $index
     *
     * @return array<int, string>
     */
    private function validateVocabularyGameParagraph(array $paragraph, int $index): array
    {
        if (!isset($paragraph['gameType']) || $paragraph['gameType'] === '') {
            return ["Vocabulary-game paragraph at index {$index} must have 'gameType'"];
        }

        return [];
    }
}
