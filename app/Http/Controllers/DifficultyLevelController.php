<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Education\DifficultyLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles difficulty level-related operations.
 */
class DifficultyLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $levels = DifficultyLevel::all();

            return response()->json($levels);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve difficulty levels', [
                'action' => 'difficulty_level_index_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to fetch difficulty levels',
            ], 500);
        }
    }
}
