<?php

declare(strict_types=1);

/**
 * Unit tests for DatabaseLogsProvider.
 */

use App\Models\Additional\Log;
use App\Services\Log\DatabaseLogsProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new DatabaseLogsProvider();

    Log::query()->delete();

    Log::create(['channel' => 'db', 'level' => 'error', 'message' => 'Something failed', 'user_id' => 1, 'created_at' => now()]);
    Log::create(['channel' => 'db', 'level' => 'info', 'message' => 'User logged in', 'user_id' => 2, 'created_at' => now()]);
    Log::create(['channel' => 'db', 'level' => 'info', 'message' => 'Another info log', 'user_id' => 1, 'created_at' => now()]);
});

/**
 * provideLogs returns all rows with no filters, or filters by level when set.
 */
it('test_provide_logs', function (array $criteria, int $expectedCount): void {
    $result = $this->service->provideLogs($criteria);

    expect($result)->toHaveCount($expectedCount);
})->with(dataProviderForTestProvideLogs());

/**
 * Provides filter criteria and expected counts for testForProvideLogs.
 */
function dataProviderForTestProvideLogs(): array
{
    return [
        'no criteria → all three logs' => [[], 3],
        'level=error → one log' => [['level' => 'error'], 1],
    ];
}

/**
 * provideLogs can filter rows to a single user_id.
 */
it('test_provide_logs_by_user_id', function (): void {
    $result = $this->service->provideLogs(['user_id' => 1]);

    expect($result)->toHaveCount(2);
});

/**
 * provideLogs filters messages with a case-insensitive substring match.
 */
it('test_provide_logs_by_message_like', function (): void {
    $result = $this->service->provideLogs(['message_like' => 'logged']);

    expect($result)->toHaveCount(1);
});

/**
 * provideLogs with per_page wraps results in LengthAwarePaginator with that size.
 */
it('test_provide_logs_with_pagination', function (int $perPage, int $expectedOnFirstPage): void {
    $result = $this->service->provideLogs(['per_page' => $perPage]);

    $paginator = $result->resource;
    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($paginator->count())->toBe($expectedOnFirstPage);
})->with(dataProviderForTestProvideLogsWithPagination());

/**
 * Provides per-page values and expected first-page counts for testForProvideLogsWithPagination.
 */
function dataProviderForTestProvideLogsWithPagination(): array
{
    return [
        'per_page=2 → 2 items on first page' => [2, 2],
        'per_page=10 → all 3 on first page' => [10, 3],
    ];
}
