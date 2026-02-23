<?php

declare(strict_types=1);

namespace App\Services\Contracts\Log;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Interface for log provider.
 */
interface LogsProviderInterface
{
    /**
     * Provide logs based on given criteria.
     *
     * @param array $criteria Criteria for filtering logs
     *
     * @return AnonymousResourceCollection Retrieved logs collection
     */
    public function provideLogs(array $criteria): AnonymousResourceCollection;
}
