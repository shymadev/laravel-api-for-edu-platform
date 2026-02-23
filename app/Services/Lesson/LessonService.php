<?php

declare(strict_types=1);

namespace App\Services\Lesson;

use App\DTO\Lesson\CreateLessonDTO;
use App\DTO\Lesson\UpdateLessonDTO;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Contracts\Lesson\LessonServiceInterface;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use ArrayIterator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LessonService implements LessonServiceInterface
{
    use Traits\ParagraphValidatorTrait;
    use Traits\ParagraphAudioProcessingTrait;

    /**
     * Constructs a new LessonService instance.
     *
     * @param AudioStorageInterface $audioStorage
     * @param TTSServiceInterface   $ttsService
     */
    public function __construct(
        protected readonly AudioStorageInterface $audioStorage,
        protected readonly TTSServiceInterface $ttsService,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getLessonById(int $id): Lesson
    {
        return Lesson::with(['topic'])->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createLesson(CreateLessonDTO $dto): Lesson
    {
        $data = $dto->toArray();
        $data['content'] = $this->processAudioInParagraphs($data['content'], $data['files'] ?? null);

        return Lesson::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLesson(Lesson $lesson, UpdateLessonDTO $dto): Lesson
    {
        $data = $dto->toArray();
        unset($data['id']);
        $files = $data['files'] ?? new ArrayIterator([]);

        $this->cleanupUnusedAudio(
            $lesson->content,
            $data['content'] ?? null
        );

        $data['content'] = $this->processAudioInParagraphs($data['content'], $files);

        $lesson->update($data);
        $lesson->refresh();

        return $lesson;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteLesson(Lesson $lesson): bool
    {
        return (bool) $lesson->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getLessonsByTopic(Topic $topic): AnonymousResourceCollection
    {
        $query = Lesson::query();

        $fetchedCollection = $query->where('topic_id', '=', $topic->id)
          ->orderBy('weight')
          ->get();

        return LessonResource::collection($fetchedCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function validateContent(?array $content): array
    {
        if ($content === null || $content === []) {
            return [];
        }

        $errors = [];

        foreach ($content as $index => $paragraph) {
            $type = $paragraph['type'] ?? null;

            if ($type === null) {
                $errors[] = "Paragraph at index {$index} is missing 'type' field";

                continue;
            }

            $paragraphErrors = $this->validateParagraphByType($type, $paragraph, $index);
            $errors = array_merge($errors, $paragraphErrors);
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Lesson $lesson): Lesson
    {
        $lesson->update(['is_active' => true]);
        $lesson->save();

        return $lesson->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(Lesson $lesson): Lesson
    {
        $lesson->update(['is_active' => false]);
        $lesson->save();

        return $lesson->refresh();
    }
}
