<?php

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\Lesson;
use App\Models\Education\Topic;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\TTSServiceInterface;
use App\Services\Lesson\Enums\VocabularyGameType;
use App\Services\Lesson\Traits\ParagraphAudioProcessingTrait;
use ArrayIterator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    use ParagraphAudioProcessingTrait;

    public function __construct(
        protected readonly AudioStorageInterface $audioStorage,
        protected readonly TTSServiceInterface $ttsService,
    ) {
    }

    public function run(): void
    {
        $courses = $this->getCoursesStructure();

        foreach ($courses as $courseData) {
            $course = Course::create([
                'title' => $courseData['title'],
                'language' => 'en',
                'description' => $courseData['description'],
                'difficulty_level_id' => $courseData['level_id'],
                'is_premium' => true,
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

                    foreach ($subtopicData['lessons'] as $idx => $lessonTitle) {
                        $rawContent = $this->generateDiverseLessonContent($courseData['level_code'], $lessonTitle);

                        // Process Audio (TTS) using the Trait
                        // Pass empty ArrayIterator because we are not uploading files, only generating TTS
                        $processedContent = $this->processAudioInParagraphs($rawContent, new ArrayIterator([]));

                        Lesson::create([
                            'topic_id' => $subtopic->id,
                            'title' => $lessonTitle,
                            'weight' => $idx + 1,
                            'is_active' => true,
                            'content' => $processedContent,
                        ]);
                    }
                }
            }
        }
    }

    private function generateDiverseLessonContent(string $level, string $title): array
    {
        $content = [];
        $order = 0;

        // 1. INTRO: Rich Text Theory (Editor.js)
        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => "Welcome to: $title", 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => "In this comprehensive lesson, we will master the nuances of <b>$title</b> suitable for <b>$level</b> level learners. We have prepared a variety of interactive games and exercises for you."]],
                    ['type' => 'paragraph', 'data' => ['text' => "Key objectives:"]],
                    ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => ['Expand vocabulary', 'Improve listening skills', 'Practice speaking', 'Master grammar context']]],
                ],
            ]),
        ];

        // 2. VIDEO: Educational Context
        $content[] = [
            'type' => 'video',
            'order' => $order++,
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        // 3. VOCABULARY: Phrases
        $content[] = [
            'type' => 'phrases',
            'order' => $order++,
            'phrases' => [
                ['text' => 'Fundamental Concept', 'translation' => 'Фундаментальная концепция'],
                ['text' => 'Practical Skill', 'translation' => 'Практический навык'],
                ['text' => 'Daily Practice', 'translation' => 'Ежедневная практика'],
                ['text' => 'Language Mastery', 'translation' => 'Владение языком'],
                ['text' => 'Complex Idea', 'translation' => 'Сложная идея'],
            ],
        ];

        // 4. LISTENING (TTS Enabled)
        $content[] = [
            'type' => 'audio',
            'order' => $order++,
            'content' => [
                'type' => 'tts',
                'text' => "Listen carefully. Mastering $title requires dedication. You should practice every day to see real results. Do not be afraid of making mistakes, as they are part of learning.",
            ],
        ];

        // 5. GAME: Word Scramble
        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => 'word-scramble',
            'words' => ['Practice', 'Mastery', 'Concept', 'Skill', 'Idea'],
        ];

        // 6. MID-LESSON: Advanced Theory (Text Block)
        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => "Deep Dive into $title", 'level' => 3]],
                    ['type' => 'paragraph', 'data' => ['text' => "Now let's explore this topic further. Context is everything."]],
                    ['type' => 'quote', 'data' => ['text' => "To have another language is to possess a second soul.", 'caption' => 'Charlemagne', 'alignment' => 'left']],
                    ['type' => 'paragraph', 'data' => ['text' => "Remember these advanced tips when using your new skills."]],
                ],
            ]),
        ];

        // 7. PRACTICE: Speech Recognition
        $content[] = [
            'type' => 'speech-recognition',
            'order' => $order++,
            'content' => [
                'instructions' => 'Read the sentence aloud clearly:',
                'targetPhrase' => "Practice is the key to mastery",
            ],
        ];

        // 8. PRACTICE: Matching Pairs
        $content[] = [
            'type' => 'matching',
            'order' => $order++,
            'shuffleRight' => true,
            'pairs' => [
                ['id' => (string)Str::uuid(), 'left' => 'Mastery', 'right' => 'Expertise'],
                ['id' => (string)Str::uuid(), 'left' => 'Concept', 'right' => 'Idea'],
                ['id' => (string)Str::uuid(), 'left' => 'Dedication', 'right' => 'Commitment'],
                ['id' => (string)Str::uuid(), 'left' => 'Result', 'right' => 'Outcome'],
            ],
        ];

        // 9. GAME: Listen & Write (TTS Enabled)
        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => VocabularyGameType::LISTEN_WRITE->value,
            'listenItems' => [
                [
                    'type' => 'tts',
                    'text' => 'Opportunity',
                    'correctText' => 'Opportunity',
                ],
                [
                    'type' => 'tts',
                    'text' => 'Challenge',
                    'correctText' => 'Challenge',
                ],
            ],
        ];

        // 10. PRACTICE: Fill Gaps
        $content[] = [
            'type' => 'fill-gaps',
            'order' => $order++,
            'text' => "To learn effectively, you must [GAP] regularly and review your [GAP].",
            'gaps' => [
                [
                    'position' => 0,
                    'correctAnswers' => ['practice', 'study'],
                    'hint' => 'Action',
                ],
                [
                     'position' => 1,
                     'correctAnswers' => ['notes', 'lessons', 'mistakes'],
                     'hint' => 'Something you write down',
                ],
            ],
        ];

        // 11. GAME: Guess Word
        $content[] = [
            'type' => 'vocabulary-game',
            'order' => $order++,
            'gameType' => 'guess-word',
            'guessItems' => [
                [
                    'word' => 'Teacher',
                    'hint' => 'A person who helps you learn.',
                ],
                [
                    'word' => 'Dictionary',
                    'hint' => 'A book with definitions of words.',
                ],
            ],
        ];

        // 12. SUMMARY: Final Text Block
        $content[] = [
            'type' => 'text',
            'order' => $order++,
            'content' => json_encode([
                'time' => time() * 1000,
                'version' => '2.30.0',
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => "Lesson Summary", 'level' => 3]],
                    ['type' => 'paragraph', 'data' => ['text' => "Excellent work today. You have completed the comprehensive lesson on $title."]],
                ],
            ]),
        ];

        // 13. CATEGORIZATION
        $content[] = [
            'type' => 'categorization',
            'order' => $order++,
            'categories' => [
                ['id' => 'cat_easy', 'name' => 'Simple', 'color' => '#8bc34a'],
                ['id' => 'cat_hard', 'name' => 'Complex', 'color' => '#ff5722'],
            ],
            'items' => [
                ['id' => (string)Str::uuid(), 'text' => 'Hello', 'correctCategory' => 'cat_easy'],
                ['id' => (string)Str::uuid(), 'text' => 'Phenomenon', 'correctCategory' => 'cat_hard'],
                ['id' => (string)Str::uuid(), 'text' => 'Cat', 'correctCategory' => 'cat_easy'],
                ['id' => (string)Str::uuid(), 'text' => 'Hypothesis', 'correctCategory' => 'cat_hard'],
            ],
        ];

        // 14. TEST: Final Quiz
        $content[] = [
            'type' => 'test',
            'order' => $order++,
            'questions' => [
                [
                    'text' => "Which activity helps with pronunciation?",
                    'options' => [
                        'Speaking',
                        'Sleeping',
                        'Silent reading',
                    ],
                    'correctOptions' => [0],
                ],
                [
                    'text' => "What is an 'odd one out' game?",
                    'options' => [
                        'Finding the different item',
                        'Matching pairs',
                    ],
                    'correctOptions' => [0],
                ],
                 [
                    'text' => "Is practice important?",
                    'options' => [
                        'Yes, very',
                        'No, not at all',
                    ],
                    'correctOptions' => [0],
                ],
            ],
        ];

        return $content;
    }

    private function getCoursesStructure(): array
    {
        return [
           [
               'title' => 'Basic English Communication (A1)',
               'level_code' => 'A1',
               'level_id' => 1,
               'description' => 'Start speaking English from day one. Essential grammar and vocabulary.',
               'topics' => $this->generateTopics('A1'),
           ],
           [
               'title' => 'Elementary English (A2)',
               'level_code' => 'A2',
               'level_id' => 2,
               'description' => 'Build confidence in daily conversations and broaden your vocabulary.',
               'topics' => $this->generateTopics('A2'),
           ],
           [
               'title' => 'Intermediate Skills (B1)',
               'level_code' => 'B1',
               'level_id' => 3,
               'description' => 'Express opinions, describe experiences, and handle most travel situations.',
               'topics' => $this->generateTopics('B1'),
           ],
           [
               'title' => 'Upper Intermediate Mastery (B2)',
               'level_code' => 'B2',
               'level_id' => 4,
               'description' => 'Fluent and spontaneous communication with native speakers.',
               'topics' => $this->generateTopics('B2'),
           ],
           [
               'title' => 'Advanced English (C1)',
               'level_code' => 'C1',
               'level_id' => 5,
               'description' => 'Understand complex texts and meanings.',
               'topics' => $this->generateTopics('C1'),
           ],
           [
               'title' => 'Proficiency Level (C2)',
               'level_code' => 'C2',
               'level_id' => 6,
               'description' => 'Master nuances of meaning in complex situations.',
               'topics' => $this->generateTopics('C2'),
           ],
           [
               'title' => 'Business English Professional',
               'level_code' => 'B2',
               'level_id' => 4,
               'description' => 'English for meetings, negotiations, and professional correspondence.',
               'topics' => $this->generateTopics('Business'),
           ],
        ];
    }

    private function generateTopics(string $level): array
    {
        $structures = [
             'A1' => [
                 ['title' => 'Introduction', 'subtopics' => [
                     ['title' => 'Greetings', 'lessons' => ['Saying Hello', 'Introductions']],
                     ['title' => 'Numbers', 'lessons' => ['Numbers 1-10', 'Numbers 11-100']],
                     ['title' => 'Alphabet', 'lessons' => ['Letters A-M', 'Letters N-Z']],
                 ]],
                 ['title' => 'Family & Friends', 'subtopics' => [
                     ['title' => 'Family Members', 'lessons' => ['Immediate Family', 'Extended Family']],
                     ['title' => 'Describing People', 'lessons' => ['Appearance', 'Personality']],
                 ]],
                  ['title' => 'In the House', 'subtopics' => [
                     ['title' => 'Rooms', 'lessons' => ['Kitchen & Living Room', 'Bedroom & Bathroom']],
                     ['title' => 'Furniture', 'lessons' => ['Basic Furniture', 'Household items']],
                 ]],
                  ['title' => 'Food', 'subtopics' => [
                     ['title' => 'Meals', 'lessons' => ['Breakfast', 'Dinner']],
                     ['title' => 'Vegetables', 'lessons' => ['Common Vegetables', 'Fruits']],
                 ]],
                 ['title' => 'Time', 'subtopics' => [
                     ['title' => 'Days', 'lessons' => ['Days of the Week', 'Months']],
                     ['title' => 'Clock', 'lessons' => ['Telling Time', 'Dates']],
                 ]],
             ],
             'A2' => [
                  ['title' => 'Daily Routine', 'subtopics' => [
                     ['title' => 'Morning', 'lessons' => ['Waking up', 'Breakfast habits']],
                     ['title' => 'Evening', 'lessons' => ['After work', 'Weekend plans']],
                 ]],
                 ['title' => 'Travel', 'subtopics' => [
                     ['title' => 'Transport', 'lessons' => ['Bus & Train', 'At the Airport']],
                     ['title' => 'Hotels', 'lessons' => ['Checking In', 'Complaining']],
                 ]],
                  ['title' => 'Health', 'subtopics' => [
                     ['title' => 'Body Parts', 'lessons' => ['Head & Face', 'Body']],
                     ['title' => 'Illness', 'lessons' => ['Visiting a Doctor', 'Symptoms']],
                 ]],
                  ['title' => 'Shopping', 'subtopics' => [
                     ['title' => 'Clothes', 'lessons' => ['Sizes & Colors', 'Trying on']],
                     ['title' => 'Groceries', 'lessons' => ['Supermarket', 'Prices']],
                 ]],
                 ['title' => 'Holidays', 'subtopics' => [
                     ['title' => 'Christmas', 'lessons' => ['Traditions', 'Presents']],
                     ['title' => 'Summer', 'lessons' => ['Beach', 'Activities']],
                 ]],
             ],
             'B1' => [
                  ['title' => 'Experiences', 'subtopics' => [
                     ['title' => 'Past Events', 'lessons' => ['Memorable trips', 'Childhood memories']],
                     ['title' => 'Hopes', 'lessons' => ['Future plans', 'Dreams']],
                 ]],
                  ['title' => 'Technology', 'subtopics' => [
                     ['title' => 'Computers', 'lessons' => ['Internet', 'Social Media']],
                     ['title' => 'Devices', 'lessons' => ['Smartphones', 'Gadgets']],
                 ]],
                  ['title' => 'Environment', 'subtopics' => [
                     ['title' => 'Weather', 'lessons' => ['Climate', 'Forecasting']],
                     ['title' => 'Nature', 'lessons' => ['Animals', 'Landscapes']],
                 ]],
                 ['title' => 'Culture', 'subtopics' => [
                     ['title' => 'Music', 'lessons' => ['Genres', 'Concerts']],
                     ['title' => 'Books', 'lessons' => ['Genres of Books', 'Reviews']],
                 ]],
                  ['title' => 'Work', 'subtopics' => [
                     ['title' => 'Jobs', 'lessons' => ['Professions', 'Work Environment']],
                     ['title' => 'Skills', 'lessons' => ['Soft Skills', 'Hard Skills']],
                 ]],
             ],
             'B2' => [
                  ['title' => 'Society', 'subtopics' => [
                     ['title' => 'Crime & Law', 'lessons' => ['The Court', 'Police']],
                     ['title' => 'Politics', 'lessons' => ['Elections', 'Government']],
                 ]],
                  ['title' => 'Workplace', 'subtopics' => [
                     ['title' => 'Interviews', 'lessons' => ['Job Interview', 'CV Writing']],
                     ['title' => 'Office Life', 'lessons' => ['Colleagues', 'Meetings']],
                 ]],
                  ['title' => 'Education', 'subtopics' => [
                     ['title' => 'University', 'lessons' => ['Lectures', 'Exams']],
                     ['title' => 'Online Learning', 'lessons' => ['Pros & Cons', 'Platforms']],
                 ]],
                 ['title' => 'Feelings', 'subtopics' => [
                     ['title' => 'Emotions', 'lessons' => ['Stress', 'Happiness']],
                     ['title' => 'Relationships', 'lessons' => ['Conflict', 'Friendship']],
                 ]],
                  ['title' => 'Health', 'subtopics' => [
                     ['title' => 'Mental Health', 'lessons' => ['Mindfulness', 'Therapy']],
                     ['title' => 'Nutrition', 'lessons' => ['Diet', 'Vitamins']],
                 ]],
             ],
             'C1' => [
                  ['title' => 'Abstract Ideas', 'subtopics' => [
                     ['title' => 'Time', 'lessons' => ['Time Management', 'Philosophy of Time']],
                     ['title' => 'Beliefs', 'lessons' => ['Superstitions', 'Values']],
                 ]],
                  ['title' => 'Science', 'subtopics' => [
                     ['title' => 'Space', 'lessons' => ['Universe', 'Exploration']],
                     ['title' => 'Genetics', 'lessons' => ['DNA', 'Ethics']],
                 ]],
                  ['title' => 'Media', 'subtopics' => [
                     ['title' => 'News', 'lessons' => ['Bias', 'Reporting']],
                     ['title' => 'Advertising', 'lessons' => ['Persuasion', 'Marketing']],
                 ]],
                 ['title' => 'Global Issues', 'subtopics' => [
                     ['title' => 'Poverty', 'lessons' => ['Causes', 'Solutions']],
                     ['title' => 'Migration', 'lessons' => ['Economics', 'Culture']],
                 ]],
                  ['title' => 'Future', 'subtopics' => [
                     ['title' => 'AI', 'lessons' => ['Transformation', 'Risks']],
                     ['title' => 'Urbanism', 'lessons' => ['Megacities', 'Transportion']],
                 ]],
             ],
             'C2' => [
                  ['title' => 'Literature', 'subtopics' => [
                     ['title' => 'Poetry', 'lessons' => ['Metaphor', 'Rhyme']],
                     ['title' => 'Prose', 'lessons' => ['Novel structure', 'Character']],
                 ]],
                  ['title' => 'Linguistics', 'subtopics' => [
                     ['title' => 'History of English', 'lessons' => ['Origins', 'Evolution']],
                     ['title' => 'Dialects', 'lessons' => ['Accents', 'Variations']],
                 ]],
                  ['title' => 'Psychology', 'subtopics' => [
                     ['title' => 'Cognition', 'lessons' => ['Memory', 'Perception']],
                     ['title' => 'Behavior', 'lessons' => ['Habits', 'Motivation']],
                 ]],
                 ['title' => 'Art', 'subtopics' => [
                     ['title' => 'History', 'lessons' => ['Movements', 'Techniques']],
                     ['title' => 'Criticism', 'lessons' => ['Interpretation', 'Value']],
                 ]],
                  ['title' => 'Philosophy', 'subtopics' => [
                     ['title' => 'Ethics', 'lessons' => ['Morality', 'Dilemmas']],
                     ['title' => 'Logic', 'lessons' => ['Reasoning', 'Fallacies']],
                 ]],
             ],
             'Business' => [
                  ['title' => 'Communication', 'subtopics' => [
                     ['title' => 'Emails', 'lessons' => ['Formal', 'Informal']],
                     ['title' => 'Calls', 'lessons' => ['Scheduling', 'Conference']],
                 ]],
                  ['title' => 'Management', 'subtopics' => [
                     ['title' => 'Leadership', 'lessons' => ['Styles', 'Delegation']],
                     ['title' => 'Projects', 'lessons' => ['Planning', 'Agile']],
                 ]],
                  ['title' => 'Finance', 'subtopics' => [
                     ['title' => 'Banking', 'lessons' => ['Accounts', 'Loans']],
                     ['title' => 'Markets', 'lessons' => ['Stocks', 'Trends']],
                 ]],
                  ['title' => 'Marketing', 'subtopics' => [
                     ['title' => 'Strategy', 'lessons' => ['Branding', 'Target Audience']],
                     ['title' => 'Digital', 'lessons' => ['SEO', 'Social Media']],
                 ]],
                 ['title' => 'HR', 'subtopics' => [
                     ['title' => 'Hiring', 'lessons' => ['Recruitment', 'Onboarding']],
                     ['title' => 'Culture', 'lessons' => ['Team Building', 'Environment']],
                 ]],
             ],
         ];

        return $structures[$level] ?? [];
    }
}
