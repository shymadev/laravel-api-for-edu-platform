<?php

declare(strict_types=1);

namespace App\Http\Controllers\Entities;

/**
 * Value object for list pagination (per-page size).
 */
final readonly class PaginationOptions
{
    /**
     * Constructs a new PaginationOptions instance.
     *
     * @param int $perPage
     */
    public function __construct(
        public int $perPage = 15,
    ) {
    }
}
