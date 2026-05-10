<?php

declare(strict_types=1);

/**
 * Feature tests for phrase routes.
 */

use App\Models\Education\Phrase;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\EspokeTranscriptionService;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
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

function makePhraseUser(int $roleId = Role::USER_ROLE_ID): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password_hash' => Hash::make('secret'),
        'role_id' => $roleId,
    ]);
}

function makePhrase(string $text = 'Hello', bool $isPhrasebook = true): Phrase
{
    return Phrase::create([
        'text' => $text,
        'translation' => 'Привет',
        'is_phrasebook' => $isPhrasebook,
    ]);
}

/**
 * GET /api/phrases returns 200 with phrasebook entries.
 */
it('test_index_returns_200_with_phrases', function (): void {
    makePhrase('Hello');
    makePhrase('Goodbye');

    $response = $this->getJson('/api/phrases');

    $response->assertOk()
        ->assertJsonStructure(['data']);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(2);
});

/**
 * GET /api/phrases excludes non-phrasebook phrases.
 */
it('test_index_excludes_non_phrasebook_phrases', function (): void {
    makePhrase('Phrasebook entry', true);
    makePhrase('Lesson phrase', false);

    $response = $this->getJson('/api/phrases');

    $texts = collect($response->json('data'))->pluck('text')->toArray();
    expect($texts)->toContain('Phrasebook entry')
        ->and($texts)->not->toContain('Lesson phrase');
});

/**
 * GET /api/phrases-categories returns distinct non-null topics.
 */
it('test_categories_returns_distinct_topics', function (): void {
    Phrase::create(['text' => 'Hi', 'is_phrasebook' => true, 'topic' => 'greetings']);
    Phrase::create(['text' => 'Bye', 'is_phrasebook' => true, 'topic' => 'greetings']);
    Phrase::create(['text' => 'Train', 'is_phrasebook' => true, 'topic' => 'travel']);

    $response = $this->getJson('/api/phrases-categories');

    $response->assertOk()
        ->assertJsonStructure(['categories']);

    expect(count($response->json('categories')))->toBe(2);
});

/**
 * GET /api/phrases/{id} by a moderator returns 200 with phrase data.
 */
it('test_show_by_moderator_returns_200', function (): void {
    $mod = makePhraseUser(Role::MODERATOR_ROLE_ID);
    $phrase = makePhrase();

    $response = $this->actingAs($mod)->getJson("/api/phrases/{$phrase->id}");

    $response->assertOk()
        ->assertJsonFragment(['text' => 'Hello']);
});

/**
 * GET /api/phrases/{id} by a regular user returns 403.
 */
it('test_show_by_regular_user_returns_403', function (): void {
    $user = makePhraseUser(Role::USER_ROLE_ID);
    $phrase = makePhrase();

    $response = $this->actingAs($user)->getJson("/api/phrases/{$phrase->id}");

    $response->assertForbidden();
});

/**
 * POST /api/phrases by moderator creates a phrase (mocking TTS/storage).
 */
it('test_store_by_moderator_returns_201', function (): void {
    $mod = makePhraseUser(Role::MODERATOR_ROLE_ID);

    $fakeFile = UploadedFile::fake()->create('audio.wav', 10, 'audio/wav');

    $this->mock(TTSService::class, function ($mock) use ($fakeFile): void {
        $mock->shouldReceive('generateAudio')->once()->andReturn($fakeFile);
    });
    $this->mock(AudioStorageService::class, function ($mock): void {
        $mock->shouldReceive('upload')->once()->andReturn('https://storage.example.com/audio.wav');
    });
    $this->mock(EspokeTranscriptionService::class, function ($mock): void {
        $mock->shouldReceive('transcribe')->once()->andReturn('HH AH L OW');
    });

    $response = $this->actingAs($mod)->postJson('/api/phrases', [
        'text' => 'Hello World',
        'translation' => 'Привет Мир',
    ]);

    $response->assertCreated()
        ->assertJsonFragment(['text' => 'Hello World']);
});

/**
 * POST /api/phrases by a regular user returns 403.
 */
it('test_store_by_regular_user_returns_403', function (): void {
    $user = makePhraseUser(Role::USER_ROLE_ID);

    $response = $this->actingAs($user)->postJson('/api/phrases', [
        'text' => 'Forbidden',
        'translation' => 'Запрещено',
    ]);

    $response->assertForbidden();
});

// ─── DELETE /api/phrases/{id} ─────────────────────────────────────────────

/**
 * DELETE /api/phrases/{id} by moderator returns 204.
 */
it('test_destroy_by_moderator_returns_204', function (): void {
    $mod = makePhraseUser(Role::MODERATOR_ROLE_ID);
    $phrase = makePhrase();

    $this->mock(AudioStorageService::class, function ($mock): void {
        $mock->shouldReceive('delete')->andReturn(true);
    });
    $this->mock(TTSService::class);
    $this->mock(EspokeTranscriptionService::class);

    $response = $this->actingAs($mod)->deleteJson("/api/phrases/{$phrase->id}");

    $response->assertNoContent();
    expect(Phrase::find($phrase->id))->toBeNull();
});

/**
 * DELETE /api/phrases/{id} by regular user returns 403.
 */
it('test_destroy_by_regular_user_returns_403', function (): void {
    $user = makePhraseUser(Role::USER_ROLE_ID);
    $phrase = makePhrase();

    $response = $this->actingAs($user)->deleteJson("/api/phrases/{$phrase->id}");

    $response->assertForbidden();
});

/**
 * POST /api/phrases/resolve returns existing phrase when text matches.
 */
it('test_resolve_returns_existing_phrase', function (): void {
    $existing = makePhrase('hello');

    $this->mock(TTSService::class, function ($mock): void {
        $mock->shouldNotReceive('generateAudio');
    });
    $this->mock(AudioStorageService::class);
    $this->mock(EspokeTranscriptionService::class);

    $response = $this->postJson('/api/phrases/resolve', [
        'text' => 'hello',
        'translation' => 'Привет',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['id' => $existing->id]);
});

/**
 * POST /api/phrases/resolve returns 422 when text or translation is missing.
 */
it('test_resolve_returns_422_when_fields_missing', function (array $payload): void {
    $response = $this->postJson('/api/phrases/resolve', $payload);

    $response->assertUnprocessable();
})->with([
    'missing text' => [['translation' => 'Привет']],
    'missing translation' => [['text' => 'hello']],
    'both missing' => [[]],
]);
