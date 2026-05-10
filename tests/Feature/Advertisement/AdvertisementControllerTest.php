<?php

declare(strict_types=1);

/**
 * Feature tests for advertisement routes.
 */

use App\Models\Additional\Advertisement;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
            ['id' => Role::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => Role::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );

    Storage::fake('minio');
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeAdUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeAd(bool $isActive = true): Advertisement
{
    return Advertisement::create([
        'image_url' => 'https://example.com/ad.png',
        'url' => 'https://example.com',
        'is_active' => $isActive,
    ]);
}

// ─── GET /api/advertisements/active ───────────────────────────────────────

/**
 * GET /api/advertisements/active returns 200 with only active ads.
 */
it('test_active_returns_200_with_only_active_ads', function (): void {
    makeAd(isActive: true);
    makeAd(isActive: false);

    $response = $this->getJson('/api/advertisements/active');

    $response->assertOk()
        ->assertJsonStructure(['data']);
    $ids = collect($response->json('data'))->pluck('is_active')->toArray();
    expect(array_unique($ids))->toBe([true]);
});

/**
 * GET /api/advertisements/active returns empty when no active ads.
 */
it('test_active_returns_empty_when_no_active_ads', function (): void {
    makeAd(isActive: false);

    $response = $this->getJson('/api/advertisements/active');

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

// ─── GET /api/advertisements ───────────────────────────────────────────────

/**
 * GET /api/advertisements returns 200 with all advertisements.
 */
it('test_index_returns_200_with_all_ads', function (): void {
    makeAd(isActive: true);
    makeAd(isActive: false);

    $response = $this->getJson('/api/advertisements');

    $response->assertOk()
        ->assertJsonStructure(['data']);
    expect(count($response->json('data')))->toBe(2);
});

// ─── POST /api/advertisements ─────────────────────────────────────────────

/**
 * POST /api/advertisements by admin returns 201 with created ad.
 */
it('test_store_by_admin_returns_201', function (): void {
    $admin = makeAdUser(Role::ADMIN_ROLE_ID);
    $image = UploadedFile::fake()->create('ad.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($admin)->post('/api/advertisements', [
        'image' => $image,
        'url' => 'https://example.com',
    ]);

    $response->assertCreated();
});

/**
 * POST /api/advertisements by regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makeAdUser(Role::USER_ROLE_ID);
    $image = UploadedFile::fake()->create('ad.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($user)->post('/api/advertisements', [
        'image' => $image,
        'url' => 'https://example.com',
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/advertisements without auth returns 401.
 */
it('test_store_unauthenticated_returns_401', function (): void {
    $response = $this->postJson('/api/advertisements', [
        'url' => 'https://example.com',
    ]);

    $response->assertUnauthorized();
});

// ─── PUT /api/advertisements/{advertisement} ──────────────────────────────

/**
 * PUT /api/advertisements/{advertisement} by admin returns 200.
 */
it('test_update_by_admin_returns_200', function (): void {
    $admin = makeAdUser(Role::ADMIN_ROLE_ID);
    $ad = makeAd();

    $response = $this->actingAs($admin)->putJson("/api/advertisements/{$ad->id}", [
        'url' => 'https://updated.com',
    ]);

    $response->assertOk();
});

/**
 * PUT /api/advertisements/{advertisement} by regular user returns 403.
 */
it('test_update_by_regular_user_returns_403', function (): void {
    $user = makeAdUser();
    $ad = makeAd();

    $response = $this->actingAs($user)->putJson("/api/advertisements/{$ad->id}", [
        'url' => 'https://hijacked.com',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/advertisements/{advertisement} ───────────────────────────

/**
 * DELETE /api/advertisements/{advertisement} by admin returns 204.
 */
it('test_destroy_by_admin_returns_204', function (): void {
    $admin = makeAdUser(Role::ADMIN_ROLE_ID);
    $ad = makeAd();

    $response = $this->actingAs($admin)->deleteJson("/api/advertisements/{$ad->id}");

    $response->assertNoContent();
    expect(Advertisement::find($ad->id))->toBeNull();
});

/**
 * DELETE /api/advertisements/{advertisement} by regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makeAdUser();
    $ad = makeAd();

    $response = $this->actingAs($user)->deleteJson("/api/advertisements/{$ad->id}");

    $response->assertForbidden();
});

// ─── Publish / Unpublish ──────────────────────────────────────────────────

/**
 * PUT /api/advertisements/{advertisement}/publish by moderator activates the ad.
 */
it('test_publish_by_moderator_activates_ad', function (): void {
    $mod = makeAdUser(Role::MODERATOR_ROLE_ID);
    $ad = makeAd(isActive: false);

    $response = $this->actingAs($mod)->putJson("/api/advertisements/{$ad->id}/publish");

    $response->assertOk();
    expect(Advertisement::find($ad->id)->is_active)->toBeTrue();
});

/**
 * PUT /api/advertisements/{advertisement}/unpublish by moderator deactivates the ad.
 */
it('test_unpublish_by_moderator_deactivates_ad', function (): void {
    $mod = makeAdUser(Role::MODERATOR_ROLE_ID);
    $ad = makeAd(isActive: true);

    $response = $this->actingAs($mod)->putJson("/api/advertisements/{$ad->id}/unpublish");

    $response->assertOk();
    expect(Advertisement::find($ad->id)->is_active)->toBeFalse();
});

/**
 * PUT /api/advertisements/{advertisement}/publish by regular user returns 403.
 */
it('test_publish_by_regular_user_returns_403', function (): void {
    $user = makeAdUser();
    $ad = makeAd(isActive: false);

    $response = $this->actingAs($user)->putJson("/api/advertisements/{$ad->id}/publish");

    $response->assertForbidden();
});
