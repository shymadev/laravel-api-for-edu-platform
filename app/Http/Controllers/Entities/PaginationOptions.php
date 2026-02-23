<?php

declare(strict_types=1);

namespace App\Http\Controllers\Entities;

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
