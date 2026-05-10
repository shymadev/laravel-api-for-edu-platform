<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

/**
 * DTO for a test question and its options (nested under test paragraph).
 */
class TestQuestion extends BaseParagraph
{
    public string $text;

    /**
     * @var TestOption[]
     */
    public array $options;

    /**
     * @param string $text
     * @param TestOption[] $options
     *
     * @return void
     */
    public function __construct(string $text, array $options)
    {
        $this->text = $text;
        $this->options = $options;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'options' => array_map(fn (TestOption $option) => $option->toArray(), $this->options),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        /** @var array<int, string> $testOptions */
        $testOptions = $data['options'] ?? [];

        /** @var array<int, int> $correctOptions */
        $correctOptions = $data['correctOptions'] ?? [];

        $optionsObjects = [];

        foreach ($testOptions as $key => $optionText) {
            if (in_array($key, array_values($correctOptions), true)) {
                $optionsObjects[] = new TestOption(
                    text: $optionText,
                    isCorrect: true,
                );

                break;
            }

            $optionsObjects[] = new TestOption(
                text: $optionText,
                isCorrect: false,
            );
        }

        return new self(
            $data['text'],
            $optionsObjects,
        );
    }
}
