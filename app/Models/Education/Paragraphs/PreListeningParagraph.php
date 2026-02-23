<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a pre-listening task paragraph in a lesson.
 */
class PreListeningParagraph extends BaseParagraph
{
    public string $taskType;
    public string $title;
    public string $instructions;
    public ?array $vocabularyWords;
    public ?array $questions;

    public function __construct(
        int $order,
        string $taskType,
        string $title,
        string $instructions,
        ?array $vocabularyWords = null,
        ?array $questions = null
    ) {
        $this->order = $order;
        $this->type = 'pre-listening';
        $this->taskType = $taskType;
        $this->title = $title;
        $this->instructions = $instructions;
        $this->vocabularyWords = $vocabularyWords;
        $this->questions = $questions;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'taskType' => $this->taskType,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'vocabularyWords' => $this->vocabularyWords,
            'questions' => $this->questions,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['taskType'] ?? 'prediction',
            $data['title'] ?? '',
            $data['instructions'] ?? '',
            $data['vocabularyWords'] ?? null,
            $data['questions'] ?? null
        );
    }
}
