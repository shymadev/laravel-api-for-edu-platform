<?php

declare(strict_types=1);

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Entities\PaginationOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorInterface;

trait PaginatorTrait
{
    /**
     * Extract pagination options from the request.
     *
     * @param Request $request
     *
     * @return PaginationOptions|false
     */
    public function extractPaginationOptions(Request $request): PaginationOptions | false
    {
        $perPage = (int) $request->query('per_page', '15');

        return $perPage <= 0 ? false : new PaginationOptions($perPage);
    }

    /**
     * Paginate the query based on the pagination options.
     *
     * @param Builder           $query
     * @param PaginationOptions $paginationOptions
     *
     * @return LengthAwarePaginatorInterface
     */
    public function paginateQuery(Builder $query, PaginationOptions $paginationOptions): LengthAwarePaginatorInterface
    {
        return $query->paginate($paginationOptions->perPage);
    }
}
