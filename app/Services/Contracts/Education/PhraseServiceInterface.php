<?php

declare(strict_types=1);

namespace App\Services\Contracts\Education;

use App\DTO\Phrase\CreatePhraseDTO;
use App\DTO\Phrase\UpdatePhraseDTO;
use App\Models\Education\Phrase;

interface PhraseServiceInterface
{
    /**
     * Get all categories.
     *
     * @return array<string>
     */
    public function getAllCategories(): array;

    /**
     * Get phrase by id.
     *
     * @param int $id
     *
     * @return Phrase
     */
    public function getPhraseById(int $id): Phrase;

    /**
     * Create phrase.
     *
     * @param CreatePhraseDTO $dto
     *
     * @return Phrase
     */
    public function createPhrase(CreatePhraseDTO $dto): Phrase;

    /**
     * Update phrase.
     *
     * @param Phrase          $phrase
     * @param UpdatePhraseDTO $dto
     *
     * @return Phrase
     */
    public function updatePhrase(Phrase $phrase, UpdatePhraseDTO $dto): Phrase;

    /**
     * Delete phrase.
     *
     * @param int $id
     *
     * @return void
     */
    public function deletePhrase(int $id): void;

    /**
     * Regenerate audio for phrase.
     *
     * @param int $id
     *
     * @return Phrase
     */
    public function regenerateAudio(int $id): Phrase;
}
