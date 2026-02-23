<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class TestParagraph extends BaseParagraph
{
    /** @var TestQuestion[] */
    public array $questions;

    /**
     * @param int            $order
     * @param TestQuestion[] $questions
     */
    public function __construct(int $order, array $questions)
    {
        $this->order = $order;
        $this->type = 'test';
        $this->questions = $questions;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'questions' => array_map(fn (TestQuestion $question) => $question->toArray(), $this->questions),
        ];
    }

    public static function fromArray(array $data): static
    {
        $questions = array_map(
            fn (array $questionData) => TestQuestion::fromArray($questionData),
            $data['questions'] ?? []
        );

        return new self(
            $data['order'],
            $questions
        );
    }
}
