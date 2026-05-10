<?php

declare(strict_types=1);

namespace App\Services\Lesson\Traits;

use App\Services\Lesson\Enums\ParagraphType;
use App\Services\Lesson\Enums\VocabularyGameType;
use Illuminate\Http\UploadedFile;

trait ParagraphAudioProcessingTrait
{
    /**
     * Clean up audio files that are no longer referenced.
     *
     * @param ?array $oldContent
     * @param ?array $newContent
     *
     * @return integer Number of files deleted
     */
    public function cleanupUnusedAudio(?array $oldContent, ?array $newContent): int
    {
        $audioUrlsToDelete = array_diff(
            $this->extractAudioUrls($oldContent),
            $this->extractAudioUrls($newContent),
        );

        $deletedCount = 0;

        foreach ($audioUrlsToDelete as $url) {
            if ($this->audioStorage->delete($url)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Process audio files in lesson paragraphs.
     *
     * @param array $content
     * @param ?\ArrayIterator $files
     *
     * @return array
     */
    public function processAudioInParagraphs(array &$content, ?\ArrayIterator $files): array
    {
        if ($files === null || $content === []) {
            return $content;
        }

        foreach ($content as $key => $paragraph) {
            if ($paragraph['type'] === ParagraphType::AUDIO->value) {
                $content[$key] = $this->processAudioParagraph($paragraph, $files);
            }
            if ($paragraph['type'] === ParagraphType::VOCABULARY_GAME->value) {
                $content[$key] = $this->processVocabularyGameParagraph($paragraph, $files);
            }
        }

        return $content;
    }

    /**
     * Whether to call the TTS service for `tts` paragraph items (seeders may disable when TTS is down).
     *
     * @return boolean
     */
    protected function isLessonTtsGenerationEnabled(): bool
    {
        return true;
    }

    /**
     * Process an audio paragraph to handle file uploads or TTS generation.
     *
     * @param array $paragraph
     * @param \ArrayIterator $files
     *
     * @return array
     */
    protected function processAudioParagraph(array $paragraph, \ArrayIterator $files): array
    {
        if (!isset($paragraph['content'])) {
            return [];
        }

        $content = $paragraph['content'];

        if ($content['type'] === 'stored_audio' && isset($content['file_key'])) {
            $file = $files[$content['file_key']];
            $fileToUpload = new UploadedFile(
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                null,
                true,
            );
            $uploadPath = $this->audioStorage->upload($fileToUpload, 'lessons/audio');
            unset($content['file_key']);
            $content['audio_url'] = $uploadPath;
        } elseif ($content['type'] === 'tts' && isset($content['text']) && $this->isLessonTtsGenerationEnabled()) {
            if (!isset($content['audio_url']) || $content['audio_url'] === '') {
                $fileToUpload = $this->ttsService->generateAudio($content['text']);
                $uploadPath = $this->audioStorage->upload($fileToUpload, 'lessons/audio_paragraphs');
                $content['audio_url'] = $uploadPath;
            }
        }

        $paragraph['content'] = $content;

        return $paragraph;
    }

    /**
     * Process a vocabulary game paragraph to handle audio uploads or TTS generation.
     *
     * @param array $paragraph
     * @param \ArrayIterator $files
     *
     * @return array
     */
    protected function processVocabularyGameParagraph(array $paragraph, \ArrayIterator $files): array
    {
        if ($paragraph['gameType'] !== VocabularyGameType::LISTEN_WRITE->value) {
            return $paragraph;
        }

        $listenItems = $paragraph['listenItems'];

        foreach ($listenItems as $key => $item) {
            if (isset($item['file_key'])) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $files[$item['file_key']];
                $fileToUpload = new UploadedFile(
                    $file->getPathname(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    null,
                    true,
                );

                $uploadPath = $this->audioStorage->upload($fileToUpload, 'lessons/vocabulary_games');
                unset($item['file_key']);
                $item['audio_url'] = $uploadPath;
                $listenItems[$key] = $item;
            } else {
                $itemType = $item['type'] ?? 'tts';
                if ($itemType === 'tts' && isset($item['text']) && $this->isLessonTtsGenerationEnabled()) {
                    if (!isset($item['audio_url']) || $item['audio_url'] === '') {
                        $fileToUpload = $this->ttsService->generateAudio($item['text']);
                        $uploadPath = $this->audioStorage->upload($fileToUpload, 'lessons/vocabulary_games');
                        $item['audio_url'] = $uploadPath;
                        $item['type'] = 'tts';
                        $listenItems[$key] = $item;
                    }
                }
            }
        }

        $paragraph['listenItems'] = $listenItems;

        return $paragraph;
    }

    /**
     * Extract all audio URLs from lesson content.
     *
     * @param ?array $content
     *
     * @return array<int, string>
     */
    private function extractAudioUrls(?array $content): array
    {
        $audioUrls = [];

        foreach ($content as $paragraph) {
            if ($paragraph['type'] === ParagraphType::AUDIO->value) {
                if (isset($paragraph['content']['audio_url'])) {
                    $audioUrls[] = $paragraph['content']['audio_url'];
                }
            }
            if ($paragraph['type'] === ParagraphType::VOCABULARY_GAME->value) {
                if ($paragraph['gameType'] === VocabularyGameType::LISTEN_WRITE->value) {
                    foreach ($paragraph['listenItems'] as $item) {
                        if (isset($item['audio_url'])) {
                            $audioUrls[] = $item['audio_url'];
                        }
                    }
                }
            }
        }

        return $audioUrls;
    }
}
