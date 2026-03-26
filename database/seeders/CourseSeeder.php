<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\DifficultyLevel;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use App\Services\Lesson\Enums\VocabularyGameType;
use App\Services\Lesson\Traits\ParagraphAudioProcessingTrait;
use ArrayIterator;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use ParagraphAudioProcessingTrait;

    private bool $lessonTtsEnabled = true;

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $payloadCache = null;

    public function __construct(
        protected readonly AudioStorageInterface $audioStorage,
        protected readonly TTSServiceInterface $ttsService,
    ) {
    }

    protected function isLessonTtsGenerationEnabled(): bool
    {
        return $this->lessonTtsEnabled;
    }

    public function run(): void
    {
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        $skipByEnv = filter_var(env('SEED_SKIP_TTS', false), FILTER_VALIDATE_BOOLEAN);
        $this->lessonTtsEnabled = ! $skipByEnv && $this->ttsService->isHealthy();

        if ($skipByEnv) {
            $this->command->warn('SEED_SKIP_TTS is set: course lessons will be seeded without TTS audio.');
        } elseif (! $this->lessonTtsEnabled) {
            $this->command->warn(
                'TTS service is not reachable (check container `tts` on app_network). Lessons will be seeded without generated audio.'
            );
        }

        $levelIds = DifficultyLevel::query()
            ->whereIn('name', ['A1', 'A2', 'B1', 'B2', 'C1'])
            ->pluck('id', 'name')
            ->all();

        foreach (['A1', 'A2', 'B1', 'B2', 'C1'] as $need) {
            if (! isset($levelIds[$need])) {
                $this->command->error("Difficulty level \"{$need}\" is missing. Run DifficultyLevelSeeder first.");

                return;
            }
        }

        $courses = $this->getCoursesStructure($levelIds);

        foreach ($courses as $courseData) {
            $course = Course::create([
                'title' => $courseData['title'],
                'language' => 'en',
                'description' => $courseData['description'],
                'difficulty_level_id' => $courseData['level_id'],
                'is_premium' => $courseData['is_premium'] ?? true,
                'is_active' => true,
            ]);

            foreach ($courseData['topics'] as $topicData) {
                $topic = Topic::create([
                    'course_id' => $course->id,
                    'title' => $topicData['title'],
                    'is_active' => true,
                ]);

                foreach ($topicData['subtopics'] as $subtopicData) {
                    $subtopic = Topic::create([
                        'course_id' => $course->id,
                        'title' => $subtopicData['title'],
                        'is_active' => true,
                        'parent_id' => $topic->id,
                    ]);

                    foreach ($subtopicData['lessons'] as $idx => $lessonData) {
                        $payloadKey = $lessonData['payload'];
                        $payload = $this->getPayload($payloadKey);
                        $rawContent = $this->buildLessonContentFromPayload($payload);

                        $processedContent = $this->processAudioInParagraphs($rawContent, new ArrayIterator([]));

                        Lesson::create([
                            'topic_id' => $subtopic->id,
                            'title' => $lessonData['title'],
                            'weight' => $idx + 1,
                            'is_active' => true,
                            'content' => $processedContent,
                        ]);

                        unset($processedContent, $rawContent, $payload);
                    }
                }
            }
        }

        $this->command->info('CourseSeeder finished: '.Course::count().' courses in database (this seeder defines 17 courses, 170 lessons).');
    }

    /**
     * @return array<string, mixed>
     */
    private function getPayload(string $key): array
    {
        self::$payloadCache ??= require __DIR__.'/course_payloads.php';

        if (! isset(self::$payloadCache[$key])) {
            throw new \InvalidArgumentException("Unknown lesson payload: {$key}");
        }

        return self::$payloadCache[$key];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildLessonContentFromPayload(array $data): array
    {
        $content = [];
        $order = 0;

        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => $data['intro_header'], 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => $data['intro_paragraph']]],
                    ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => $data['objectives']]],
                ],
            ]),
        ];

        $content[] = [
            'type' => 'video',
            'order' => $order++,
            'url' => $data['video_url'],
        ];

        $content[] = [
            'type' => 'phrases',
            'order' => $order++,
            'phrases' => $data['phrases'],
        ];

        $content[] = [
            'type' => 'audio',
            'order' => $order++,
            'content' => [
                'type' => 'tts',
                'text' => $data['listening_text'],
            ],
        ];

        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => 'word-scramble',
            'words' => $data['scramble_words'],
        ];

        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => $data['theory_header'], 'level' => 3]],
                    ['type' => 'paragraph', 'data' => ['text' => $data['theory_paragraph']]],
                    ['type' => 'quote', 'data' => ['text' => $data['quote'], 'caption' => $data['quote_author'], 'alignment' => 'left']],
                ],
            ]),
        ];

        $content[] = [
            'type' => 'speech-recognition',
            'order' => $order++,
            'content' => [
                'instructions' => 'Read the sentence aloud clearly:',
                'targetPhrase' => $data['speech_phrase'],
            ],
        ];

        $content[] = [
            'type' => 'matching',
            'order' => $order++,
            'shuffleRight' => true,
            'pairs' => $data['matching_pairs'],
        ];

        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => VocabularyGameType::LISTEN_WRITE->value,
            'listenItems' => $data['listen_write_items'],
        ];

        $content[] = [
            'type' => 'fill-gaps',
            'order' => $order++,
            'text' => $data['fill_gaps_text'],
            'gaps' => $data['fill_gaps'],
        ];

        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => 'guess-word',
            'guessItems' => $data['guess_items'],
        ];

        $content[] = [
            'type' => 'categorization',
            'order' => $order++,
            'categories' => $data['categories'],
            'items' => $data['categorization_items'],
        ];

        $content[] = [
            'type' => 'test',
            'order' => $order++,
            'questions' => $data['quiz_questions'],
        ];

        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => 'Lesson Summary', 'level' => 3]],
                    ['type' => 'paragraph', 'data' => ['text' => $data['summary']]],
                ],
            ]),
        ];

        return $content;
    }

    /**
     * @param  array<string, int>  $levelIds  name => id
     * @return list<array<string, mixed>>
     */
    private function getCoursesStructure(array $levelIds): array
    {
        /** @var array{courses: list<array<string, mixed>>} $catalog */
        $catalog = require __DIR__.'/course_catalog.php';

        $courses = [];
        foreach ($catalog['courses'] as $course) {
            $topics = [];
            foreach ($course['themes'] as $theme) {
                $topics[] = [
                    'title' => $theme['title'],
                    'subtopics' => [
                        [
                            'title' => $theme['title'].' — units',
                            'lessons' => array_values(array_map(
                                static fn (array $l): array => [
                                    'title' => $l['title'],
                                    'payload' => $l['payload'],
                                ],
                                $theme['lessons'],
                            )),
                        ],
                    ],
                ];
            }

            $courses[] = [
                'title' => $course['title'],
                'description' => $course['description'],
                'level_id' => $levelIds[$course['level']],
                'is_premium' => $course['is_premium'],
                'topics' => $topics,
            ];
        }

        return $courses;
    }
}
