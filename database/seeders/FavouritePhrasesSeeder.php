<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\FavoritePhrase;
use App\Models\Education\Phrase;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Seeds favourite phrases for users.
 *
 * Data is driven by database/seeders/data/users/favourite_phrases.json.
 * Each entry: { user_index, phrase_count, learned_ratio }.
 *
 * phrase_count random phrases are drawn from the phrases table
 * and assigned to the user; ~learned_ratio of them are flagged is_learned.
 */
class FavouritePhrasesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding favourite phrases...');

        $usersData = $this->loadJson('users/users.json');
        $favouriteData = $this->loadJson('users/favourite_phrases.json');

        if ($usersData === [] || $favouriteData === []) {
            $this->command->warn('Required JSON files missing — skipping FavouritePhrasesSeeder.');

            return;
        }

        // Pre-load all phrase IDs into memory (avoid N+1 in loop)
        $allPhraseIds = Phrase::pluck('id')->shuffle();

        if ($allPhraseIds->isEmpty()) {
            $this->command->warn('No phrases in database — run PhraseSeeder first.');

            return;
        }

        $userMap = $this->buildUserMap($usersData);

        $created = 0;
        $skipped = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($favouriteData));
        $bar->start();

        foreach ($favouriteData as $entry) {
            $userIndex = $entry['user_index'];
            $phraseCount = (int) ($entry['phrase_count'] ?? 5);
            $learnedRatio = (float) ($entry['learned_ratio'] ?? 0.0);

            if (!isset($usersData[$userIndex])) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $email = $usersData[$userIndex]['email'];
            $user = $userMap->get($email);

            if ($user === null) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $picked = $allPhraseIds->shuffle()->take($phraseCount);

            foreach ($picked as $phraseId) {
                if (FavoritePhrase::query()->where('user_id', $user->id)->where('phrase_id', $phraseId)->exists()) {
                    continue;
                }

                try {
                    $isLearned = (mt_rand() / mt_getrandmax()) < $learnedRatio;

                    FavoritePhrase::create([
                        'user_id' => $user->id,
                        'phrase_id' => $phraseId,
                        'is_learned' => $isLearned,
                    ]);
                    $created++;
                } catch (\Throwable $e) {
                    Log::error('FavouritePhrasesSeeder: error', [
                        'user' => $email,
                        'phrase_id' => $phraseId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Favourite phrases seeded: {$created} created, {$skipped} users skipped.");
    }

    /**
     * @param array<int, array<string, mixed>> $usersData
     *
     * @return Collection<string, User>
     */
    private function buildUserMap(array $usersData): Collection
    {
        $emails = collect($usersData)->pluck('email');

        return User::whereIn('email', $emails)->get()->keyBy('email');
    }

    /**
     * @param string $relativePath
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadJson(string $relativePath): array
    {
        $path = database_path('seeders/data/' . $relativePath);

        if (!File::exists($path)) {
            $this->command->warn("JSON file not found: {$path}");

            return [];
        }

        return json_decode(File::get($path), true) ?? [];
    }
}
