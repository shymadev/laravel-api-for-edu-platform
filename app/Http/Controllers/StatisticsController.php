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
    /**
     * Constructs a new StatisticsController instance.
     *
     * @param \App\Services\StatisticsProvider $statisticsProvider
     *
     * @return void
     */
    public function __construct(
        protected readonly StatisticsProvider $statisticsProvider,
    ) {
    }

    /**
     * Retrieve statistics data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        return response()->json($this->statisticsProvider->getStatistics()->toArray());
    }
}
