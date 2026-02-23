<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Services\Contracts\Log\DatabaseLogsProviderInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller for providing log-related functionalities.
 */
class LogsController extends Controller
{
    use PaginatorTrait;

    /**
     * Constructs the LogsController.
     */
    public function __construct(protected readonly DatabaseLogsProviderInterface $databaseLogsProvider)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $paginationOptions = $this->extractPaginationOptions($request);

            $criteria = $this->extractCriteriaOptions($request);

            if ($paginationOptions instanceof PaginationOptions) {
                $criteria['per_page'] = $paginationOptions->perPage;
            }

            return $this->databaseLogsProvider->provideLogs($criteria);
        } catch (\Throwable $exception) {
            Log::channel('db')->error('Failed to retrieve logs', ['exception' => $exception]);

            return response()->json(['error' => 'Failed to retrieve logs'], 500);
        }
    }

    protected function extractCriteriaOptions(Request $request): array
    {
        $criteria = [];

        $level = $request->query('level');
        if (! is_null($level) && $level !== '') {
            $criteria['level'] = $level;
        }

        $userId = $request->query('user_id');
        if (! is_null($userId) && $userId !== '') {
            $criteria['user_id'] = (int) $userId;
        }

        $message = $request->query('message_like');
        if (! is_null($message) && $message !== '') {
            $criteria['message_like'] = $message;
        }

        return $criteria;
    }
}
