<?php

declare(strict_types=1);

namespace App\Http\Controllers\Entities;

/**
 * Value object for list search query text.
 */
final readonly class SearchOptions
{
    /**
     * Constructs a new SearchOptions instance.
     *
     * @param string $searchQuery
     */
    public function __construct(
        public string $searchQuery = '',
    ) {
    }
}
