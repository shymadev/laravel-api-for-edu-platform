<?php

declare(strict_types=1);

/**
 * Feature tests for difficulty level routes.
 */

use App\Models\Education\DifficultyLevel;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

// ─── GET /api/difficulty-levels ───────────────────────────────────────────

/**
 * GET /api/difficulty-levels returns 200 with all difficulty levels (public route).
 */
it('test_index_returns_200_with_difficulty_levels', function (): void {
    DifficultyLevel::create(['name' => 'Beginner', 'value' => 1]);
    DifficultyLevel::create(['name' => 'Intermediate', 'value' => 2]);
    DifficultyLevel::create(['name' => 'Advanced', 'value' => 3]);

    $response = $this->getJson('/api/difficulty-levels');

    $response->assertOk();
    expect(count($response->json()))->toBe(3);
});

/**
 * GET /api/difficulty-levels returns empty array when no levels exist.
 */
it('test_index_returns_empty_when_no_levels', function (): void {
    $response = $this->getJson('/api/difficulty-levels');

    $response->assertOk();
    expect($response->json())->toBe([]);
});

/**
 * GET /api/difficulty-levels is accessible without authentication.
 */
it('test_index_is_publicly_accessible', function (): void {
    $response = $this->getJson('/api/difficulty-levels');

    $response->assertOk();
});
