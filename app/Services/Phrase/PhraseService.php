<?php

declare(strict_types=1);

namespace App\Services\Phrase;

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
class PhraseService implements PhraseServiceInterface
{
    /**
     * Constructs Phrase service object.
     *
     * @param TTSServiceInterface $ttsService
     */
    public function __construct(
        protected readonly TTSServiceInterface $ttsService,
        protected readonly AudioStorageInterface $audioStorage,
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

    /**
     * {@inheritDoc}
     */
    public function getPhraseById(int $id): Phrase
    {
        return Phrase::findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function deletePhrase(int $id): void
    {
        $phrase = $this->getPhraseById($id);

        if ($phrase->audio !== null) {
            Storage::disk('public')->delete($phrase->audio);
        }

        $phrase->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function regenerateAudio(int $id): Phrase
    {
        $phrase = $this->getPhraseById($id);

        try {
            if ($phrase->audio !== null) {
                Storage::disk('public')->delete($phrase->audio);
            }

            $audioPath = $this->ttsService->generateAudio($phrase->text);
            $phrase->update(['audio' => $audioPath]);
        } catch (\Exception $e) {
            Log::error("Failed to regenerate audio for phrase {$id}: " . $e->getMessage());

            throw $e;
        }

        return $phrase->fresh();
    }
}
