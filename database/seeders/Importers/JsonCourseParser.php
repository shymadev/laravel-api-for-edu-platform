<?php

declare(strict_types=1);

namespace Database\Seeders\Importers;

use Database\Seeders\Importers\Contracts\CourseDataParserInterface;

/**
 * Parses a JSON file into a normalized course data array.
 */
class JsonCourseParser implements CourseDataParserInterface
{
    /**
     * Parse a JSON course file and return a normalized course array.
     *
     * @param string $filePath
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException when the file cannot be read, contains invalid JSON,
     *                           or is missing required fields
     */
    public function parse(string $filePath): array
    {
        $raw = file_get_contents($filePath);

        if ($raw === false) {
            throw new \RuntimeException("Cannot read file: {$filePath}");
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "Invalid JSON in {$filePath}: " . json_last_error_msg(),
            );
        }

        $this->validate($data, $filePath);

        return $data;
    }

    /**
     * Return true when this parser handles JSON files.
     *
     * @param string $filePath
     */
    public function supports(string $filePath): bool
    {
        return str_ends_with(strtolower($filePath), '.json');
    }

    /**
     * @param array<string, mixed> $data
     * @param string $filePath
     *
     * @throws \RuntimeException when a required field is missing or empty
     */
    private function validate(array $data, string $filePath): void
    {
        $required = ['title', 'difficulty_level', 'topics'];

        foreach ($required as $field) {
            $value = $data[$field] ?? null;

            if ($value === null || $value === '' || $value === []) {
                throw new \RuntimeException(
                    "Missing required field '{$field}' in: {$filePath}",
                );
            }
        }
    }
}
