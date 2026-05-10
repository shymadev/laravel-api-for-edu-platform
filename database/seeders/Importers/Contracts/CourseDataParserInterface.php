<?php

declare(strict_types=1);

namespace Database\Seeders\Importers\Contracts;

/**
 * Contract for parsing course definition files into a normalized data array.
 */
interface CourseDataParserInterface
{
    /**
     * Parse a course data file and return a normalized course array.
     *
     * @param string $filePath
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException on parse or validation failure
     */
    public function parse(string $filePath): array;

    /**
     * Return true when this parser handles the given file path.
     *
     * @param string $filePath
     */
    public function supports(string $filePath): bool;
}
