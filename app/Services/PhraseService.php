<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Phrase\CreatePhraseDTO;
use App\DTO\Phrase\UpdatePhraseDTO;
use App\Models\Education\Phrase;
use App\Services\Contracts\Education\PhraseServiceInterface;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service to manage phrases.
 */
readonly class PhraseService
{
    /**
     * Constructs Phrase service object.
     *
     * @param TTSServiceInterface $ttsService
     */
    public function __construct(
        protected TTSServiceInterface   $ttsService,
        protected AudioStorageInterface $audioStorage,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getAllCategories(): array
    {
        return Phrase::query()
            ->select('topic')
            ->distinct()
            ->whereNotNull('topic')
            ->pluck('topic')
            ->toArray();
    }

    public function getPhraseById(int $id): Phrase
    {
        return Phrase::findOrFail($id);
    }

    public function createPhrase(CreatePhraseDTO $dto): Phrase
    {
        $phrase = Phrase::create([
            'text' => $dto->text,
            'translation' => $dto->translation,
            'difficulty_level_id' => $dto->difficultyLevelId,
            'topic' => $dto->topic,
            'audio' => null,
        ]);

        try {
            $generatedAudio = $this->ttsService->generateAudio($dto->text);
            $audioPath = $this->audioStorage->upload($generatedAudio, 'phrases');
            $phrase->update(['audio' => $audioPath]);
        } catch (\Exception $e) {
            Log::error("Failed to generate audio for phrase {$phrase->id}: " . $e->getMessage());
        }

        return $phrase->fresh();
    }

    public function updatePhrase(Phrase $phrase, UpdatePhraseDTO $dto): Phrase
    {
        $data = array_filter($dto->toArray(), fn ($value) => $value !== null);

        if (isset($data['text'])) {
            try {
                $audioPath = $this->ttsService->generateAudio($data['text']);

                if ($phrase->audio !== null) {
                    $this->audioStorage->delete($phrase->audio);
                }

                $data['audio'] = $audioPath;
            } catch (\Exception $e) {
                Log::error("Failed to regenerate audio for phrase {$data['id']}: " . $e->getMessage());
            }
        }

        $phrase->update($data);

        return $phrase;
    }

    public function deletePhrase(int $id): void
    {
        $phrase = $this->getPhraseById($id);

        if ($phrase->audio !== null) {
            Storage::disk('public')->delete($phrase->audio);
        }

        $phrase->delete();
    }

}
