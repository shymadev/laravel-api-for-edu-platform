<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StatisticsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controller that handles statistics-related operations.
 */
class StatisticsController extends Controller
{
    public function __construct(
        protected readonly StatisticsProvider $statisticsProvider
    ) {
    }

    /**
     * Retrieve statistics data.
     *
     * @return array
     */
    public function getStatistics(): JsonResponse
    {
        return response()->json($this->statisticsProvider->getStatistics()->toArray());
    }
}
