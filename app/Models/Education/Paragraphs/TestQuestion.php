<?php

declare(strict_types=1);

namespace App\Models\Education\Paragraphs;

class TestQuestion extends BaseParagraph
{
    public string $text;
    /** @var TestOption[] */
    public array $options;

    /**
     * @param string       $text
     * @param TestOption[] $options
     */
    public function __construct(string $text, array $options)
    {
        $this->text = $text;
        $this->options = $options;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'options' => array_map(fn (TestOption $option) => $option->toArray(), $this->options),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function fromArray(array $data): static
    {
        /** @var array<int, string> $testOptions */
        $testOptions = $data['options'] ?? [];

        /** @var array<int, int> $testOptions */
        $correctOptions = $data['correctOptions'] ?? [];

        $optionsObjects = [];

        foreach ($testOptions as $key => $optionText) {
            if (in_array($key, array_values($correctOptions))) {
                $optionsObjects[] = new TestOption(
                    text: $optionText,
                    isCorrect: true
                );

                break;
            }

            $optionsObjects[] = new TestOption(
                text: $optionText,
                isCorrect: false
            );
        }

        return new self(
            $data['text'],
            $optionsObjects
        );
    }
}
