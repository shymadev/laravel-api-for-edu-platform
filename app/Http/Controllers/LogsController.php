<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Services\Log\DatabaseLogsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
     *
     * @param \App\Services\Log\DatabaseLogsProvider $databaseLogsProvider
     *
     * @return void
     */
    public function __construct(protected readonly DatabaseLogsProvider $databaseLogsProvider)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
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

    /**
     * Extract criteria options from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    protected function extractCriteriaOptions(Request $request): array
    {
        $criteria = [];

        $level = $request->query('level');
        if (!is_null($level) && $level !== '') {
            $criteria['level'] = $level;
        }

        $userId = $request->query('user_id');
        if (!is_null($userId) && $userId !== '') {
            $criteria['user_id'] = (int) $userId;
        }

        $message = $request->query('message_like');
        if (!is_null($message) && $message !== '') {
            $criteria['message_like'] = $message;
        }

        return $criteria;
    }
}
