<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * Represents a sentence task paragraph in a lesson.
 */
class SentenceTaskParagraph extends BaseParagraph
{
    public array $tasks;

    /**
     * @param int $order
     * @param array $tasks
     *
     * @return void
     */
    public function __construct(int $order, array $tasks)
    {
        $this->order = $order;
        $this->type = 'sentence-task';
        $this->tasks = $tasks;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'type' => $this->type,
            'tasks' => $this->tasks,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new self(
            $data['order'],
            $data['tasks'] ?? [],
        );
    }
}
