<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Phrase\CreatePhraseDTO;
use App\DTO\Phrase\UpdatePhraseDTO;
use App\Models\Education\Phrase;
use App\Services\Storage\AudioStorageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service to manage phrases.
 */
readonly class PhraseService
{
    /**
     * Constructs Phrase service object.
     *
     * @param TTSService $ttsService
     * @param AudioStorageService $audioStorage
     * @param EspokeTranscriptionService $espokeTranscriptionService
     */
    public function __construct(
        protected TTSService $ttsService,
        protected AudioStorageService $audioStorage,
        protected EspokeTranscriptionService $espokeTranscriptionService,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAllCategories(): array
    {
        return Cache::tags(['phrases'])->remember(
            'phrases.categories',
            7200,
            fn () => Phrase::query()
                ->select('topic')
                ->distinct()
                ->whereNotNull('topic')
                ->pluck('topic')
                ->toArray(),
        );
    }

    /**
     * @param int $id
     *
     * @return Phrase
     */
    public function getPhraseById(int $id): Phrase
    {
        return Cache::tags(['phrases', "phrase.{$id}"])->remember(
            "phrase.{$id}",
            3600,
            fn () => Phrase::findOrFail($id),
        );
    }

    /**
     * @param CreatePhraseDTO $dto
     *
     * @return Phrase
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

            $transcription = $this->espokeTranscriptionService->transcribe($dto->text);
            $phrase->update(['transcription' => $transcription]);
        } catch (\Exception $e) {
            Log::error("Failed to generate audio for phrase {$phrase->id}: " . $e->getMessage());
        }

        return $phrase->fresh();
    }

    /**
     * @param Phrase $phrase
     * @param UpdatePhraseDTO $dto
     *
     * @return Phrase
     */
    public function updatePhrase(Phrase $phrase, UpdatePhraseDTO $dto): Phrase
    {
        $data = array_filter($dto->toArray(), fn ($value) => $value !== null);

        if (isset($data['text'])) {
            try {
                $generatedAudio = $this->ttsService->generateAudio($data['text']);
                $newAudioPath = $this->audioStorage->upload($generatedAudio, 'phrases');
                $transcription = $this->espokeTranscriptionService->transcribe($data['text']);

                if ($phrase->audio !== null) {
                    $this->audioStorage->delete($phrase->audio);
                }

                $data['audio'] = $newAudioPath;
                $data['transcription'] = $transcription;
            } catch (\Exception $e) {
                Log::error("Failed to regenerate audio for phrase {$data['id']}: " . $e->getMessage());
            }
        }

        $phrase->update($data);

        return $phrase;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deletePhrase(int $id): void
    {
        $phrase = $this->getPhraseById($id);

        if ($phrase->audio !== null) {
            $this->audioStorage->delete($phrase->audio);
        }

        $phrase->delete();
    }

    /**
     * Find an existing phrase by text (case-insensitive) or create a non-phrasebook one.
     * Used to resolve lesson phrases so they can be favorited.
     *
     * @param string $text
     * @param string $translation
     *
     * @return Phrase
     */
    public function resolveByText(string $text, string $translation): Phrase
    {
        $phrase = Phrase::query()
            ->whereRaw('LOWER(text) = ?', [mb_strtolower($text)])
            ->first();

        if ($phrase !== null) {
            return $phrase;
        }

        $phrase = Phrase::create([
            'text' => $text,
            'translation' => $translation,
            'is_phrasebook' => false,
            'audio' => null,
        ]);

        try {
            $generatedAudio = $this->ttsService->generateAudio($text);
            $audioPath = $this->audioStorage->upload($generatedAudio, 'phrases');
            $phrase->update(['audio' => $audioPath]);

            $transcription = $this->espokeTranscriptionService->transcribe($text);
            $phrase->update(['transcription' => $transcription]);
        } catch (\Exception $e) {
            Log::error("Failed to generate audio for resolved phrase {$phrase->id}: " . $e->getMessage());
        }

        return $phrase->fresh();
    }
}
