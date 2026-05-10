<?php

declare(strict_types=1);

/**
 * Feature tests for favorite phrase routes.
 */

use App\Models\Education\FavoritePhrase;
use App\Models\Education\Phrase;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

function makeFavPhraseUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makeFavPhrase(): Phrase
{
    return Phrase::create([
        'text' => fake()->unique()->words(3, true),
        'translation' => fake()->unique()->words(3, true),
        'is_phrasebook' => true,
    ]);
}

function addToFavorites(User $user, Phrase $phrase): FavoritePhrase
{
    return FavoritePhrase::create([
        'user_id' => $user->id,
        'phrase_id' => $phrase->id,
        'is_learned' => false,
    ]);
}

// ─── GET /api/favorite-phrases ────────────────────────────────────────────

/**
 * GET /api/favorite-phrases returns 200 with user's favorites.
 */
it('test_index_returns_200_with_favorites', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();
    addToFavorites($user, $phrase);

    $response = $this->actingAs($user)->getJson('/api/favorite-phrases');

    $response->assertOk()
        ->assertJsonStructure(['data']);
    expect(count($response->json('data')))->toBe(1);
});

/**
 * GET /api/favorite-phrases returns empty collection when no favorites.
 */
it('test_index_returns_empty_when_no_favorites', function (): void {
    $user = makeFavPhraseUser();

    $response = $this->actingAs($user)->getJson('/api/favorite-phrases');

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

/**
 * GET /api/favorite-phrases returns 401 without auth.
 */
it('test_index_returns_401_unauthenticated', function (): void {
    $response = $this->getJson('/api/favorite-phrases');

    $response->assertUnauthorized();
});

/**
 * GET /api/favorite-phrases only returns the authenticated user's favorites, not others'.
 */
it('test_index_returns_only_own_favorites', function (): void {
    $user = makeFavPhraseUser();
    $other = makeFavPhraseUser();
    $phrase = makeFavPhrase();
    addToFavorites($other, $phrase);

    $response = $this->actingAs($user)->getJson('/api/favorite-phrases');

    $response->assertOk();
    expect($response->json('data'))->toBe([]);
});

// ─── POST /api/favorite-phrases/toggle ────────────────────────────────────

/**
 * POST /api/favorite-phrases/toggle adds a phrase to favorites.
 */
it('test_toggle_adds_phrase_to_favorites', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['favorited' => true]);

    expect(FavoritePhrase::where('user_id', $user->id)->where('phrase_id', $phrase->id)->exists())->toBeTrue();
});

/**
 * POST /api/favorite-phrases/toggle removes a phrase from favorites when already favorited.
 */
it('test_toggle_removes_phrase_from_favorites', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();
    addToFavorites($user, $phrase);

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['favorited' => false]);

    expect(FavoritePhrase::where('user_id', $user->id)->where('phrase_id', $phrase->id)->exists())->toBeFalse();
});

/**
 * POST /api/favorite-phrases/toggle returns 422 when phrase_id is missing.
 */
it('test_toggle_returns_422_when_phrase_id_missing', function (): void {
    $user = makeFavPhraseUser();

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['phrase_id']);
});

/**
 * POST /api/favorite-phrases/toggle returns 401 without auth.
 */
it('test_toggle_returns_401_unauthenticated', function (): void {
    $phrase = makeFavPhrase();

    $response = $this->postJson('/api/favorite-phrases/toggle', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertUnauthorized();
});

// ─── POST /api/favorite-phrases/toggle-learned ────────────────────────────

/**
 * POST /api/favorite-phrases/toggle-learned marks a favorited phrase as learned.
 */
it('test_toggle_learned_marks_phrase_as_learned', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();
    addToFavorites($user, $phrase);

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle-learned', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['is_learned' => true]);

    expect(FavoritePhrase::where('user_id', $user->id)->where('phrase_id', $phrase->id)->value('is_learned'))->toBeTrue();
});

/**
 * POST /api/favorite-phrases/toggle-learned unmarks a learned phrase.
 */
it('test_toggle_learned_unmarks_a_learned_phrase', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();
    FavoritePhrase::create([
        'user_id' => $user->id,
        'phrase_id' => $phrase->id,
        'is_learned' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle-learned', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['is_learned' => false]);
});

/**
 * POST /api/favorite-phrases/toggle-learned returns 404 when phrase not in favorites.
 */
it('test_toggle_learned_returns_404_when_phrase_not_in_favorites', function (): void {
    $user = makeFavPhraseUser();
    $phrase = makeFavPhrase();

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle-learned', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertNotFound();
});

/**
 * POST /api/favorite-phrases/toggle-learned returns 422 when phrase_id is missing.
 */
it('test_toggle_learned_returns_422_when_phrase_id_missing', function (): void {
    $user = makeFavPhraseUser();

    $response = $this->actingAs($user)->postJson('/api/favorite-phrases/toggle-learned', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['phrase_id']);
});

/**
 * POST /api/favorite-phrases/toggle-learned returns 401 without auth.
 */
it('test_toggle_learned_returns_401_unauthenticated', function (): void {
    $phrase = makeFavPhrase();

    $response = $this->postJson('/api/favorite-phrases/toggle-learned', [
        'phrase_id' => $phrase->id,
    ]);

    $response->assertUnauthorized();
});
