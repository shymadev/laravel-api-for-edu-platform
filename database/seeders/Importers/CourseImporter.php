<?php

declare(strict_types=1);

namespace Database\Seeders\Importers;

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use Database\Seeders\Importers\Contracts\CourseDataParserInterface;
use Illuminate\Support\Collection;

/**
 * Imports a single course definition into the database.
 *
 * Recursively creates Course → Topics → Subtopics → Lessons from the
 * normalized array returned by a {@see CourseDataParserInterface} implementation.
 */
class CourseImporter
{
    /**
     * @param Collection<string, int> $difficultyLevelMap
     * @param Collection<string, string> $imageUrlMap
     * @param CourseDataParserInterface $parser
     */
    public function __construct(
        private readonly CourseDataParserInterface $parser,
        private readonly Collection $difficultyLevelMap,
        private readonly Collection $imageUrlMap = new Collection(),
    ) {
    }

    /**
     * Parse and persist a course file, returning the created Course model.
     *
     * @param string $filePath
     *
     * @throws \RuntimeException when parsing fails
     */
    public function import(string $filePath): Course
    {
        $data = $this->parser->parse($filePath);

        $previewImage = isset($data['preview_image'])
            ? $this->imageUrlMap->get($data['preview_image'])
            : null;

        $course = Course::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'language' => $data['language'] ?? 'en',
            'difficulty_level_id' => $this->difficultyLevelMap->get($data['difficulty_level']),
            'is_premium' => $data['is_premium'] ?? false,
            'preview_image' => $previewImage,
            'is_active' => true,
            'is_archived' => false,
        ]);

        foreach ($data['topics'] as $topicData) {
            $this->importTopic($topicData, $course->id, null);
        }

        return $course;
    }

    /**
     * Persist a topic (and its subtopics and direct lessons) recursively.
     *
     * @param array<string, mixed> $data
     * @param int $courseId
     * @param ?int $parentId
     */
    private function importTopic(array $data, int $courseId, ?int $parentId): Topic
    {
        $topic = Topic::create([
            'course_id' => $courseId,
            'parent_id' => $parentId,
            'title' => $data['title'],
            'is_active' => true,
        ]);

        $weight = 1;

        foreach ($data['lessons'] ?? [] as $lessonData) {
            $this->importLesson($lessonData, $topic->id, $weight++);
        }

        foreach ($data['subtopics'] ?? [] as $subtopicData) {
            $this->importTopic($subtopicData, $courseId, $topic->id);
        }

        return $topic;
    }

    /**
     * Persist a single lesson under the given topic.
     *
     * @param array<string, mixed> $data
     * @param int $topicId
     * @param int $autoWeight
     */
    private function importLesson(array $data, int $topicId, int $autoWeight): Lesson
    {
        return Lesson::create([
            'topic_id' => $topicId,
            'title' => $data['title'],
            'weight' => $data['weight'] ?? $autoWeight,
            'content' => $data['content'] ?? [],
            'is_active' => true,
        ]);
    }
}
