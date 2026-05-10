<?php

declare(strict_types=1);

namespace App\Services\Lesson;

use App\DTO\Lesson\CreateLessonDTO;
use App\DTO\Lesson\UpdateLessonDTO;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use ArrayIterator;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * Manages lesson CRUD, validation, and paragraph audio processing.
 */
#[Singleton]
class LessonService
{
    use Traits\ParagraphAudioProcessingTrait;
    use Traits\ParagraphValidatorTrait;

    /**
     * Constructs a new LessonService instance.
     *
     * @param AudioStorageService $audioStorage
     * @param TTSService $ttsService
     */
    public function __construct(
        protected readonly AudioStorageService $audioStorage,
        protected readonly TTSService $ttsService,
    ) {
    }

    /**
     * Retrieve a lesson by its ID with eager-loaded topic and course.
     *
     * Result is cached in Redis under the `lessons` tag for 1 hour.
     * Throws ModelNotFoundException if the lesson does not exist.
     *
     * @param int $id
     *
     * @return Lesson
     */
    public function getLessonById(int $id): Lesson
    {
        return Cache::tags(['lessons', "lesson.{$id}"])->remember(
            "lesson.{$id}",
            3600,
            fn () => Lesson::with(['topic.course'])->findOrFail($id),
        );
    }

    /**
     * Create a new lesson, running TTS audio generation on its paragraph content.
     *
     * @param CreateLessonDTO $dto
     *
     * @return Lesson
     */
    public function createLesson(CreateLessonDTO $dto): Lesson
    {
        $data = $dto->toArray();
        $data['content'] = $this->processAudioInParagraphs($data['content'], $data['files'] ?? null);

        return Lesson::create($data);
    }

    /**
     * Update a lesson's content and metadata.
     *
     * Removes audio files from paragraphs that were dropped and runs TTS on
     * any new phrase paragraphs before persisting.
     *
     * @param Lesson $lesson
     * @param UpdateLessonDTO $dto
     *
     * @return Lesson
     */
    public function updateLesson(Lesson $lesson, UpdateLessonDTO $dto): Lesson
    {
        $data = $dto->toArray();
        unset($data['id']);
        $files = $data['files'] ?? new ArrayIterator([]);

        $this->cleanupUnusedAudio(
            $lesson->content,
            $data['content'] ?? null,
        );

        $data['content'] = $this->processAudioInParagraphs($data['content'], $files);

        $lesson->update($data);
        $lesson->refresh();

        return $lesson;
    }

    /**
     * Delete a lesson record from the database.
     *
     * @param Lesson $lesson
     *
     * @return boolean
     */
    public function deleteLesson(Lesson $lesson): bool
    {
        return (bool) $lesson->delete();
    }

    /**
     * Return all lessons for a topic ordered by weight, wrapped in a resource collection.
     *
     * Result is cached in Redis under the `lessons` tag for 30 minutes.
     *
     * @param Topic $topic
     *
     * @return AnonymousResourceCollection
     */
    public function getLessonsByTopic(Topic $topic): AnonymousResourceCollection
    {
        $lessons = Cache::tags(['lessons', "topic.{$topic->id}"])->remember(
            "lessons.topic.{$topic->id}",
            1800,
            fn () => Lesson::query()
                ->where('topic_id', $topic->id)
                ->orderBy('weight')
                ->get(),
        );

        return LessonResource::collection($lessons);
    }

    /**
     * Validate structured lesson content paragraphs.
     *
     * Returns an array of human-readable error strings, one per invalid
     * paragraph. An empty array means the content is valid.
     *
     * @param array<int, array<string, mixed>>|null $content
     *
     * @return array<int, string>
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
     * Mark a lesson as published (is_active = true).
     *
     * @param Lesson $lesson
     *
     * @return Lesson
     */
    public function publish(Lesson $lesson): Lesson
    {
        $lesson->update(['is_active' => true]);
        $lesson->save();

        return $lesson->refresh();
    }

    /**
     * Mark a lesson as unpublished (is_active = false).
     *
     * @param Lesson $lesson
     *
     * @return Lesson
     */
    public function unpublish(Lesson $lesson): Lesson
    {
        $lesson->update(['is_active' => false]);
        $lesson->save();

        return $lesson->refresh();
    }
}
