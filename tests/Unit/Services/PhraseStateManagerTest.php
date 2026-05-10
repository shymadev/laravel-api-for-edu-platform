<?php

declare(strict_types=1);

/**
 * Unit tests for PhraseStateManager.
 */

use App\DTO\ToggleFavoriteDTO;
use App\DTO\ToggleLearnedDTO;
use App\Models\Education\FavoritePhrase;
use App\Models\Education\Phrase;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\PhraseStateManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->service = new PhraseStateManager();

    Role::upsert(
        [
            ['id' => Role::USER_ROLE_ID, 'role_name' => 'user'],
            ['id' => Role::ADMIN_ROLE_ID, 'role_name' => 'admin'],
            ['id' => Role::MODERATOR_ROLE_ID, 'role_name' => 'moderator'],
        ],
        ['id'],
    );

    $this->user = User::create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password_hash' => Hash::make('password'),
        'role_id' => Role::USER_ROLE_ID,
    ]);

    $this->phrase = Phrase::create([
        'text' => 'Hello',
        'translation' => 'Привет',
        'is_phrasebook' => true,
    ]);
});

/**
 * toggleFavouriteState adds a favourite and returns true, or removes and returns false.
 */
it('test_toggle_favourite_state', function (bool $preFavourited, bool $expectedReturn): void {
    if ($preFavourited) {
        FavoritePhrase::create([
            'user_id' => $this->user->id,
            'phrase_id' => $this->phrase->id,
            'is_learned' => false,
        ]);
    }

    $dto = new ToggleFavoriteDTO(phraseId: $this->phrase->id, userId: $this->user->id);
    $result = $this->service->toggleFavouriteState($dto);

    $exists = FavoritePhrase::where('user_id', $this->user->id)
        ->where('phrase_id', $this->phrase->id)
        ->exists();

    expect($result)->toBe($expectedReturn)
        ->and($exists)->toBe($expectedReturn);
})->with(dataProviderForTestToggleFavouriteState());

/**
 * Provides pre-favourited state scenarios for testForToggleFavouriteState.
 */
function dataProviderForTestToggleFavouriteState(): array
{
    return [
        'not favourited → adds, returns true' => [false, true],
        'already favourited → removes, returns false' => [true, false],
    ];
}

/**
 * toggleLearnedState flips is_learned on an existing favourite row.
 */
it('test_toggle_learned_state', function (bool $currentLearned, bool $expectedLearned): void {
    FavoritePhrase::create([
        'user_id' => $this->user->id,
        'phrase_id' => $this->phrase->id,
        'is_learned' => $currentLearned,
    ]);

    $dto = new ToggleLearnedDTO(phraseId: $this->phrase->id, userId: $this->user->id);
    $result = $this->service->toggleLearnedState($dto);

    $dbValue = FavoritePhrase::where('user_id', $this->user->id)
        ->where('phrase_id', $this->phrase->id)
        ->value('is_learned');

    expect($result)->toBe($expectedLearned)
        ->and((bool) $dbValue)->toBe($expectedLearned);
})->with(dataProviderForTestToggleLearnedState());

/**
 * Provides learned state scenarios for testForToggleLearnedState.
 */
function dataProviderForTestToggleLearnedState(): array
{
    return [
        'not learned → marks as learned' => [false, true],
        'already learned → unlearns' => [true, false],
    ];
}

/**
 * toggleLearnedState throws when the phrase is not in favourites yet.
 */
it('test_toggle_learned_state_throws_when_not_favourited', function (): void {
    $dto = new ToggleLearnedDTO(phraseId: $this->phrase->id, userId: $this->user->id);

    expect(fn () => $this->service->toggleLearnedState($dto))
        ->toThrow(ModelNotFoundException::class);
});
