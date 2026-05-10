<?php

declare(strict_types=1);

/**
 * Feature tests for statistics routes.
 */

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

// ─── GET /api/statistics/overall ─────────────────────────────────────────

/**
 * GET /api/statistics/overall returns 200 with statistics data (public route).
 */
it('test_get_statistics_returns_200', function (): void {
    $response = $this->getJson('/api/statistics/overall');

    $response->assertOk();
});

/**
 * GET /api/statistics/overall returns numeric stats fields.
 */
it('test_get_statistics_returns_expected_structure', function (): void {
    $response = $this->getJson('/api/statistics/overall');

    $response->assertOk()
        ->assertJsonStructure([
            'active_students',
            'total_courses',
            'completed_lessons',
            'average_rating',
        ]);
});
