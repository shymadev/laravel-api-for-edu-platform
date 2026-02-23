<?php

declare(strict_types=1);

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Entities\SearchOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SearcherTrait
{
    /**
     * Extract search options from the request.
     *
     * @param Request $request
     *
     * @return SearchOptions|false
     */
    public function extractSearchOptions(Request $request): SearchOptions | false
    {
        $searchQuery = (string) $request->query('search', '');

        return $searchQuery === '' ? false : new SearchOptions($searchQuery);
    }

    /**
     * Add search conditions to the query based on the search options.
     *
     * @param Builder       $query
     * @param SearchOptions $searchOptions
     * @param array         $indexedColumns
     *
     * @return Builder
     */
    public function addSearchConditions(Builder $query, SearchOptions $searchOptions, array $indexedColumns): Builder
    {
        $searchQuery = $searchOptions->searchQuery;

        if (empty($indexedColumns)) {
            return $query;
        }

        return match (count($indexedColumns)) {
            1 => $this->addSingleColumnSearchCondition($query, $searchQuery, $indexedColumns[0]),
            default => $this->addMultiColumnSearchCondition($query, $searchQuery, $indexedColumns),
        };
    }

    /**
     * Add search conditions for multiple indexed columns.
     *
     * @param Builder $query
     * @param string  $searchQuery
     * @param array   $indexedColumns
     *
     * @return Builder
     */
    private function addSingleColumnSearchCondition(Builder $query, string $searchQuery, string $indexedColumn): Builder
    {
        return $query->where($indexedColumn, 'like', "%{$searchQuery}%");
    }

    /**
     * Add search conditions for multiple indexed columns.
     *
     * @param Builder $query
     * @param string  $searchQuery
     * @param array   $indexedColumns
     *
     * @return Builder
     */
    private function addMultiColumnSearchCondition(Builder $query, string $searchQuery, array $indexedColumns): Builder
    {
        return $query->where(static function (Builder $subQuery) use ($searchQuery, $indexedColumns) {
            foreach ($indexedColumns as $indexedColumn) {
                $subQuery->orWhere($indexedColumn, 'like', "%{$searchQuery}%");
            }
        });
    }
}
