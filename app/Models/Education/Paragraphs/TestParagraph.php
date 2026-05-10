<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for a test block (multiple questions).
 */
class TestParagraph extends BaseParagraph
{
    /**
     * @var TestQuestion[]
     */
    public array $questions;

    /**
     * @param int $order
     * @param TestQuestion[] $questions
     *
     * @return void
     */
    public function __construct(int $order, array $questions)
    {
        $this->order = $order;
        $this->type = 'test';
        $this->questions = $questions;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'questions' => array_map(fn (TestQuestion $question) => $question->toArray(), $this->questions),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $questions = array_map(
            fn (array $questionData) => TestQuestion::fromArray($questionData),
            $data['questions'] ?? [],
        );

        return new self(
            $data['order'],
            $questions,
        );
    }
}
