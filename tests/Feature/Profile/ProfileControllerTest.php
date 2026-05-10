<?php

declare(strict_types=1);

/**
 * Feature tests for profile routes.
 */

use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Storage\ImageStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

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
});

// ─── Helpers ──────────────────────────────────────────────────────────────

function makeProfileUser(int $roleId = Role::USER_ROLE_ID): User
{
    $profile = Profile::create([
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);

    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
        'profile_id' => $profile->id,
    ]);
}

// ─── GET /api/profiles/{id} ───────────────────────────────────────────────

/**
 * GET /api/profiles/{id} returns 200 with profile data for the owner.
 */
it('test_get_profile_by_id_returns_200_for_owner', function (): void {
    $user = makeProfileUser();

    $response = $this->actingAs($user)->getJson("/api/profiles/{$user->profile_id}");

    $response->assertOk();
});

/**
 * GET /api/profiles/{id} returns 403 when accessing another user's profile.
 */
it('test_get_profile_by_id_returns_403_for_other_user_profile', function (): void {
    $user = makeProfileUser();
    $other = makeProfileUser();

    $response = $this->actingAs($user)->getJson("/api/profiles/{$other->profile_id}");

    $response->assertForbidden();
});

/**
 * GET /api/profiles/{id} returns 401 without auth.
 */
it('test_get_profile_by_id_returns_401_unauthenticated', function (): void {
    $user = makeProfileUser();

    $response = $this->getJson("/api/profiles/{$user->profile_id}");

    $response->assertUnauthorized();
});

// ─── PUT /api/profiles/{id} ───────────────────────────────────────────────

/**
 * PUT /api/profiles/{id} returns 200 with updated profile for the owner.
 */
it('test_update_profile_returns_200_for_owner', function (): void {
    $user = makeProfileUser();

    $response = $this->actingAs($user)->putJson("/api/profiles/{$user->profile_id}", [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'bio' => 'Some bio',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Profile updated successfully']);
});

/**
 * PUT /api/profiles/{id} returns 403 when updating another user's profile.
 */
it('test_update_profile_returns_403_for_other_user_profile', function (): void {
    $user = makeProfileUser();
    $other = makeProfileUser();

    $response = $this->actingAs($user)->putJson("/api/profiles/{$other->profile_id}", [
        'first_name' => 'Hacker',
    ]);

    $response->assertForbidden();
});

/**
 * PUT /api/profiles/{id} returns 401 without auth.
 */
it('test_update_profile_returns_401_unauthenticated', function (): void {
    $user = makeProfileUser();

    $response = $this->putJson("/api/profiles/{$user->profile_id}", [
        'first_name' => 'Ghost',
    ]);

    $response->assertUnauthorized();
});

// ─── POST /api/profiles/{id}/avatar ──────────────────────────────────────

/**
 * POST /api/profiles/{id}/avatar returns 200 when avatar uploaded by owner.
 */
it('test_update_profile_avatar_returns_200_for_owner', function (): void {
    $user = makeProfileUser();
    $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

    $this->mock(ImageStorageService::class, function ($mock): void {
        $mock->shouldReceive('upload')->once()->andReturn('https://storage.example.com/avatar.jpg');
        $mock->shouldReceive('exists')->andReturn(false);
    });

    $response = $this->actingAs($user)->post("/api/profiles/{$user->profile_id}/avatar", [
        'avatar' => $file,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Profile avatar updated successfully']);
});

/**
 * POST /api/profiles/{id}/avatar returns 403 when accessing another user's profile.
 */
it('test_update_profile_avatar_returns_403_for_other_user_profile', function (): void {
    $user = makeProfileUser();
    $other = makeProfileUser();
    $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($user)->post("/api/profiles/{$other->profile_id}/avatar", [
        'avatar' => $file,
    ]);

    $response->assertForbidden();
});

/**
 * POST /api/profiles/{id}/avatar returns 401 without auth.
 */
it('test_update_profile_avatar_returns_401_unauthenticated', function (): void {
    $user = makeProfileUser();

    $response = $this->postJson("/api/profiles/{$user->profile_id}/avatar");

    $response->assertUnauthorized();
});
