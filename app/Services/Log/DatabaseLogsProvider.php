<?php

declare(strict_types=1);

namespace App\Services\Log;

use App\Http\Resources\Log\LogResource;
use App\Models\Additional\Log;
use App\Services\Contracts\Log\DatabaseLogsProviderInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Provides logs from the database.
 */
class DatabaseLogsProvider implements DatabaseLogsProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function provideLogs(array $criteria): AnonymousResourceCollection
    {
        $logsQuery = Log::query();

        if (isset($criteria['level'])) {
            $logsQuery->where('level', $criteria['level']);
        }

        if (isset($criteria['user_id'])) {
            $logsQuery->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['message_like'])) {
            $logsQuery->where('message', 'like', '%' . $criteria['message_like'] . '%');
        }

        $logsQuery->orderBy('created_at', 'desc');

        if (! empty($criteria['per_page'])) {
            $perPage = (int) $criteria['per_page'];
            /** @var LengthAwarePaginator $paginated */
            $paginated = $logsQuery->paginate($perPage);

            return LogResource::collection($paginated);
        }

        return LogResource::collection($logsQuery->get());
    }
}
