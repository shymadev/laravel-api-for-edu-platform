<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a sentence task paragraph in a lesson.
 */
class SentenceTaskParagraph extends BaseParagraph
{
    public array $tasks;

    public function __construct(int $order, array $tasks)
    {
        $this->order = $order;
        $this->type = 'sentence-task';
        $this->tasks = $tasks;
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'tasks' => $this->tasks,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['tasks'] ?? []
        );
    }
}
