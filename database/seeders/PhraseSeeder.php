<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\Phrase;
use App\Services\EspokeTranscriptionService;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PhraseSeeder extends Seeder
{
    protected TTSService $ttsService;

    protected AudioStorageService $audioStorage;

    protected EspokeTranscriptionService $espokeTranscriptionService;

    public function __construct()
    {
        $this->ttsService = new TTSService();
        $this->audioStorage = new AudioStorageService();
        $this->espokeTranscriptionService = new EspokeTranscriptionService();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting phrase seeding...');

        $difficultyLevels = \App\Models\Education\DifficultyLevel::pluck('id', 'name');
        $levelIdMap = [
            1 => $difficultyLevels['A1'] ?? null, // A0 → A1 (no A0 in DB)
            2 => $difficultyLevels['A1'] ?? null,
            3 => $difficultyLevels['A2'] ?? null,
            4 => $difficultyLevels['B1'] ?? null,
            5 => $difficultyLevels['B2'] ?? null,
            6 => $difficultyLevels['C1'] ?? null,
            7 => $difficultyLevels['C2'] ?? null,
        ];

        $ttsAvailable = $this->ttsService->isHealthy();

        if (!$ttsAvailable) {
            $this->command->warn('TTS service is not available. Phrases will be created without audio.');
        }

        $phrases = $this->getPhrases();
        $total = count($phrases);

        $created = 0;
        $failed = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($total);
        $progressBar->start();

        foreach ($phrases as $phraseData) {
            try {
                $audioPath = null;

                if ($ttsAvailable) {
                    $audio = $this->ttsService->generateAudio($phraseData['text']);
                    $audioPath = $this->audioStorage->upload($audio, 'phrases');
                }

                $transcription = $this->espokeTranscriptionService->transcribe($phraseData['text']);

                Phrase::create([
                    'text' => $phraseData['text'],
                    'translation' => $phraseData['translation'],
                    'difficulty_level_id' => $levelIdMap[$phraseData['difficulty_level']] ?? null,
                    'topic' => $phraseData['topic'],
                    'audio' => $audioPath,
                    'transcription' => $transcription,
                ]);

                $created++;
            } catch (\Throwable $e) {
                $failed++;

                Log::error('Failed to create phrase', [
                    'text' => $phraseData['text'],
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);

        $this->command->info('Seeding finished:');
        $this->command->info("Created: {$created} / {$total}");
        $this->command->warn("Failed: {$failed}");
    }

    /**
     * Get all phrases organized by difficulty level and topic.
     */
    protected function getPhrases(): array
    {
        return array_merge(
            $this->getA0Phrases(),
            $this->getA1Phrases(),
            $this->getA2Phrases(),
            $this->getB1Phrases(),
            $this->getB2Phrases(),
            $this->getC1Phrases(),
            $this->getC2Phrases(),
        );
    }

    /**
     * A0 Level - Complete Beginner.
     */
    protected function getA0Phrases(): array
    {
        return [
            // Greetings
            ['text' => 'Hello', 'translation' => 'Привет', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Goodbye', 'translation' => 'До свидания', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good morning', 'translation' => 'Доброе утро', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good night', 'translation' => 'Спокойной ночи', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'How are you?', 'translation' => 'Как дела?', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good afternoon', 'translation' => 'Добрый день', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good evening', 'translation' => 'Добрый вечер', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'See you later', 'translation' => 'Увидимся позже', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Welcome', 'translation' => 'Добро пожаловать', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Hi', 'translation' => 'Привет', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Bye', 'translation' => 'Пока', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Hey', 'translation' => 'Эй', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Nice to see you', 'translation' => 'Рад тебя видеть', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Take care', 'translation' => 'Береги себя', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Have a good day', 'translation' => 'Хорошего дня', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'See you soon', 'translation' => 'До скорого', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good to meet you', 'translation' => 'Рад познакомиться', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'How do you do?', 'translation' => 'Как поживаете?', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Long time no see', 'translation' => 'Давно не виделись', 'difficulty_level' => 1, 'topic' => 'Greetings'],
            ['text' => 'Good luck', 'translation' => 'Удачи', 'difficulty_level' => 1, 'topic' => 'Greetings'],

            // Basic words
            ['text' => 'Yes', 'translation' => 'Да', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'No', 'translation' => 'Нет', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Please', 'translation' => 'Пожалуйста', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Thank you', 'translation' => 'Спасибо', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Sorry', 'translation' => 'Извините', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Excuse me', 'translation' => 'Простите', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'OK', 'translation' => 'Хорошо', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Help', 'translation' => 'Помощь', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Stop', 'translation' => 'Стоп', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Wait', 'translation' => 'Подождите', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Maybe', 'translation' => 'Может быть', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Here', 'translation' => 'Здесь', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'There', 'translation' => 'Там', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Now', 'translation' => 'Сейчас', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Later', 'translation' => 'Позже', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Again', 'translation' => 'Снова', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'More', 'translation' => 'Ещё', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Less', 'translation' => 'Меньше', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Good', 'translation' => 'Хорошо', 'difficulty_level' => 1, 'topic' => 'Basic'],
            ['text' => 'Bad', 'translation' => 'Плохо', 'difficulty_level' => 1, 'topic' => 'Basic'],

            // Numbers
            ['text' => 'Zero', 'translation' => 'Ноль', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'One', 'translation' => 'Один', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Two', 'translation' => 'Два', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Three', 'translation' => 'Три', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Four', 'translation' => 'Четыре', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Five', 'translation' => 'Пять', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Six', 'translation' => 'Шесть', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Seven', 'translation' => 'Семь', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Eight', 'translation' => 'Восемь', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Nine', 'translation' => 'Девять', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Ten', 'translation' => 'Десять', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Eleven', 'translation' => 'Одиннадцать', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Twelve', 'translation' => 'Двенадцать', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Twenty', 'translation' => 'Двадцать', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Thirty', 'translation' => 'Тридцать', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Fifty', 'translation' => 'Пятьдесят', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Hundred', 'translation' => 'Сто', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Thousand', 'translation' => 'Тысяча', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'First', 'translation' => 'Первый', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Last', 'translation' => 'Последний', 'difficulty_level' => 1, 'topic' => 'Numbers'],
            ['text' => 'Half', 'translation' => 'Половина', 'difficulty_level' => 1, 'topic' => 'Numbers'],

            // Family
            ['text' => 'Mother', 'translation' => 'Мать', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Father', 'translation' => 'Отец', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Sister', 'translation' => 'Сестра', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Brother', 'translation' => 'Брат', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Baby', 'translation' => 'Малыш', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Child', 'translation' => 'Ребенок', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Man', 'translation' => 'Мужчина', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Woman', 'translation' => 'Женщина', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Boy', 'translation' => 'Мальчик', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Girl', 'translation' => 'Девочка', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Grandmother', 'translation' => 'Бабушка', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Grandfather', 'translation' => 'Дедушка', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Son', 'translation' => 'Сын', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Daughter', 'translation' => 'Дочь', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Uncle', 'translation' => 'Дядя', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Aunt', 'translation' => 'Тётя', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Cousin', 'translation' => 'Двоюродный брат/сестра', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Husband', 'translation' => 'Муж', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Wife', 'translation' => 'Жена', 'difficulty_level' => 1, 'topic' => 'Family'],
            ['text' => 'Friend', 'translation' => 'Друг', 'difficulty_level' => 1, 'topic' => 'Family'],

            // Colors
            ['text' => 'Red', 'translation' => 'Красный', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Blue', 'translation' => 'Синий', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Green', 'translation' => 'Зеленый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Yellow', 'translation' => 'Желтый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Black', 'translation' => 'Черный', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'White', 'translation' => 'Белый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Orange', 'translation' => 'Оранжевый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Pink', 'translation' => 'Розовый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Purple', 'translation' => 'Фиолетовый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Brown', 'translation' => 'Коричневый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Gray', 'translation' => 'Серый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Light blue', 'translation' => 'Голубой', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Dark', 'translation' => 'Тёмный', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Light', 'translation' => 'Светлый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Gold', 'translation' => 'Золотой', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Silver', 'translation' => 'Серебряный', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Beige', 'translation' => 'Бежевый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Turquoise', 'translation' => 'Бирюзовый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Violet', 'translation' => 'Фиолетовый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Cream', 'translation' => 'Кремовый', 'difficulty_level' => 1, 'topic' => 'Colors'],
            ['text' => 'Colorful', 'translation' => 'Красочный', 'difficulty_level' => 1, 'topic' => 'Colors'],

            // Animals
            ['text' => 'Cat', 'translation' => 'Кошка', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Dog', 'translation' => 'Собака', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Bird', 'translation' => 'Птица', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Fish', 'translation' => 'Рыба', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Horse', 'translation' => 'Лошадь', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Cow', 'translation' => 'Корова', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Pig', 'translation' => 'Свинья', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Mouse', 'translation' => 'Мышь', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Rabbit', 'translation' => 'Кролик', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Lion', 'translation' => 'Лев', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Tiger', 'translation' => 'Тигр', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Bear', 'translation' => 'Медведь', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Elephant', 'translation' => 'Слон', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Monkey', 'translation' => 'Обезьяна', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Duck', 'translation' => 'Утка', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Chicken', 'translation' => 'Курица', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Frog', 'translation' => 'Лягушка', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Snake', 'translation' => 'Змея', 'difficulty_level' => 1, 'topic' => 'Animals'],

            // Common objects
            ['text' => 'Book', 'translation' => 'Книга', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Table', 'translation' => 'Стол', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Chair', 'translation' => 'Стул', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Door', 'translation' => 'Дверь', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Window', 'translation' => 'Окно', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Phone', 'translation' => 'Телефон', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Pen', 'translation' => 'Ручка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Paper', 'translation' => 'Бумага', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Bag', 'translation' => 'Сумка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Key', 'translation' => 'Ключ', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Clock', 'translation' => 'Часы', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Lamp', 'translation' => 'Лампа', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Cup', 'translation' => 'Чашка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Plate', 'translation' => 'Тарелка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Bottle', 'translation' => 'Бутылка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Box', 'translation' => 'Коробка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Bed', 'translation' => 'Кровать', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Car', 'translation' => 'Машина', 'difficulty_level' => 1, 'topic' => 'Objects'],

            // Body parts
            ['text' => 'Head', 'translation' => 'Голова', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Eye', 'translation' => 'Глаз', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Ear', 'translation' => 'Ухо', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Nose', 'translation' => 'Нос', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Mouth', 'translation' => 'Рот', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Hand', 'translation' => 'Рука', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Foot', 'translation' => 'Нога', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Arm', 'translation' => 'Рука (предплечье)', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Leg', 'translation' => 'Нога (голень)', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Back', 'translation' => 'Спина', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Stomach', 'translation' => 'Живот', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Chest', 'translation' => 'Грудь', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Shoulder', 'translation' => 'Плечо', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Knee', 'translation' => 'Колено', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Finger', 'translation' => 'Палец', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Tooth', 'translation' => 'Зуб', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Hair', 'translation' => 'Волосы', 'difficulty_level' => 1, 'topic' => 'Body'],

            // Simple verbs
            ['text' => 'Eat', 'translation' => 'Есть', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Drink', 'translation' => 'Пить', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Sleep', 'translation' => 'Спать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Walk', 'translation' => 'Гулять', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Run', 'translation' => 'Бегать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Sit', 'translation' => 'Сидеть', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Stand', 'translation' => 'Стоять', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Come', 'translation' => 'Приходить', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Go', 'translation' => 'Идти', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'See', 'translation' => 'Видеть', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Hear', 'translation' => 'Слышать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Speak', 'translation' => 'Говорить', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Read', 'translation' => 'Читать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Write', 'translation' => 'Писать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Play', 'translation' => 'Играть', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Work', 'translation' => 'Работать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Open', 'translation' => 'Открывать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Close', 'translation' => 'Закрывать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Give', 'translation' => 'Давать', 'difficulty_level' => 1, 'topic' => 'Verbs'],
            ['text' => 'Take', 'translation' => 'Брать', 'difficulty_level' => 1, 'topic' => 'Verbs'],

            // Food basics
            ['text' => 'Bread', 'translation' => 'Хлеб', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Milk', 'translation' => 'Молоко', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Water', 'translation' => 'Вода', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Tea', 'translation' => 'Чай', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Coffee', 'translation' => 'Кофе', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Sugar', 'translation' => 'Сахар', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Salt', 'translation' => 'Соль', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Egg', 'translation' => 'Яйцо', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Meat', 'translation' => 'Мясо', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Juice', 'translation' => 'Сок', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Apple', 'translation' => 'Яблоко', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Banana', 'translation' => 'Банан', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Orange', 'translation' => 'Апельсин', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Rice', 'translation' => 'Рис', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Soup', 'translation' => 'Суп', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Butter', 'translation' => 'Масло', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Cheese', 'translation' => 'Сыр', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Potato', 'translation' => 'Картофель', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Tomato', 'translation' => 'Помидор', 'difficulty_level' => 1, 'topic' => 'Food'],
            ['text' => 'Cake', 'translation' => 'Торт', 'difficulty_level' => 1, 'topic' => 'Food'],
        ];
    }

    /**
     * A1 Level - Elementary.
     */
    protected function getA1Phrases(): array
    {
        return [
            // Introductions
            ['text' => 'My name is John', 'translation' => 'Меня зовут Джон', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'Nice to meet you', 'translation' => 'Приятно познакомиться', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I am from Russia', 'translation' => 'Я из России', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I am a student', 'translation' => 'Я студент', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I am twenty years old', 'translation' => 'Мне двадцать лет', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'Where are you from?', 'translation' => 'Откуда вы?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'What is your name?', 'translation' => 'Как вас зовут?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I live in New York', 'translation' => 'Я живу в Нью-Йорке', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'This is my friend', 'translation' => 'Это мой друг', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I am a teacher', 'translation' => 'Я учитель', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'How old are you?', 'translation' => 'Сколько вам лет?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'Do you speak English?', 'translation' => 'Вы говорите по-английски?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I speak a little English', 'translation' => 'Я немного говорю по-английски', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'What do you do?', 'translation' => 'Чем вы занимаетесь?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I work in an office', 'translation' => 'Я работаю в офисе', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I am married', 'translation' => 'Я женат/замужем', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I have two children', 'translation' => 'У меня двое детей', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'Can you repeat that?', 'translation' => 'Можете повторить?', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'I do not understand', 'translation' => 'Я не понимаю', 'difficulty_level' => 2, 'topic' => 'Introductions'],
            ['text' => 'Please speak slowly', 'translation' => 'Пожалуйста, говорите медленнее', 'difficulty_level' => 2, 'topic' => 'Introductions'],

            // Daily life
            ['text' => 'I like coffee', 'translation' => 'Я люблю кофе', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I go to school', 'translation' => 'Я хожу в школу', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'This is my house', 'translation' => 'Это мой дом', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I have a car', 'translation' => 'У меня есть машина', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I need help', 'translation' => 'Мне нужна помощь', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I wake up at seven', 'translation' => 'Я просыпаюсь в семь', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I take a shower', 'translation' => 'Я принимаю душ', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I brush my teeth', 'translation' => 'Я чищу зубы', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I get dressed', 'translation' => 'Я одеваюсь', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I have breakfast', 'translation' => 'Я завтракаю', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I watch TV', 'translation' => 'Я смотрю телевизор', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I listen to music', 'translation' => 'Я слушаю музыку', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I read a book', 'translation' => 'Я читаю книгу', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I play games', 'translation' => 'Я играю в игры', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I cook dinner', 'translation' => 'Я готовлю ужин', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I clean my room', 'translation' => 'Я убираю свою комнату', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I go to bed at ten', 'translation' => 'Я ложусь спать в десять', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I take the bus to work', 'translation' => 'Я езжу на работу на автобусе', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I have lunch at noon', 'translation' => 'Я обедаю в полдень', 'difficulty_level' => 2, 'topic' => 'Daily Life'],
            ['text' => 'I walk my dog', 'translation' => 'Я выгуливаю собаку', 'difficulty_level' => 2, 'topic' => 'Daily Life'],

            // Food
            ['text' => 'I am hungry', 'translation' => 'Я голоден', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I want pizza', 'translation' => 'Я хочу пиццу', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'Water, please', 'translation' => 'Воду, пожалуйста', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'This is delicious', 'translation' => 'Это вкусно', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I like apples', 'translation' => 'Я люблю яблоки', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I am thirsty', 'translation' => 'Я хочу пить', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'The menu, please', 'translation' => 'Меню, пожалуйста', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I would like a sandwich', 'translation' => 'Я бы хотел бутерброд', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'Can I have the check?', 'translation' => 'Можно мне счет?', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'This is too hot', 'translation' => 'Это слишком горячо', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'This is cold', 'translation' => 'Это холодное', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I do not eat meat', 'translation' => 'Я не ем мясо', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I am allergic to nuts', 'translation' => 'У меня аллергия на орехи', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'Can I have more bread?', 'translation' => 'Можно ещё хлеба?', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I prefer tea to coffee', 'translation' => 'Я предпочитаю чай кофе', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'The food is very good here', 'translation' => 'Здесь очень хорошая еда', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I want to order dessert', 'translation' => 'Я хочу заказать десерт', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'Do you have vegetarian options?', 'translation' => 'У вас есть вегетарианские блюда?', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'The portion is too small', 'translation' => 'Порция слишком маленькая', 'difficulty_level' => 2, 'topic' => 'Food'],
            ['text' => 'I would like a table for two', 'translation' => 'Я бы хотел столик на двоих', 'difficulty_level' => 2, 'topic' => 'Food'],

            // Time
            ['text' => 'What time is it?', 'translation' => 'Который час?', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Today is Monday', 'translation' => 'Сегодня понедельник', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'It is morning', 'translation' => 'Сейчас утро', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'See you tomorrow', 'translation' => 'Увидимся завтра', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Yesterday was Sunday', 'translation' => 'Вчера было воскресенье', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'It is three o clock', 'translation' => 'Сейчас три часа', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'I am late', 'translation' => 'Я опаздываю', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Wait a minute', 'translation' => 'Подождите минуту', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'What day is it?', 'translation' => 'Какой сегодня день?', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Next week', 'translation' => 'На следующей неделе', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Last year', 'translation' => 'В прошлом году', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'In the morning', 'translation' => 'Утром', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'In the evening', 'translation' => 'Вечером', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'At the weekend', 'translation' => 'На выходных', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Every day', 'translation' => 'Каждый день', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Sometimes', 'translation' => 'Иногда', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Always', 'translation' => 'Всегда', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Never', 'translation' => 'Никогда', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Soon', 'translation' => 'Скоро', 'difficulty_level' => 2, 'topic' => 'Time'],
            ['text' => 'Right now', 'translation' => 'Прямо сейчас', 'difficulty_level' => 2, 'topic' => 'Time'],

            // Shopping
            ['text' => 'How much is this?', 'translation' => 'Сколько это стоит?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I want to buy this', 'translation' => 'Я хочу купить это', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you have this in blue?', 'translation' => 'У вас есть это в синем?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I need a bag', 'translation' => 'Мне нужна сумка', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Can I try this on?', 'translation' => 'Могу я это примерить?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'This is too expensive', 'translation' => 'Это слишком дорого', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you accept credit cards?', 'translation' => 'Вы принимаете кредитные карты?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Where is the nearest store?', 'translation' => 'Где ближайший магазин?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I am just looking', 'translation' => 'Я просто смотрю', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you have a smaller size?', 'translation' => 'У вас есть меньший размер?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Can I get a receipt?', 'translation' => 'Можно мне чек?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I would like to return this', 'translation' => 'Я хотел бы вернуть это', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Is there a discount?', 'translation' => 'Есть ли скидка?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Where is the fitting room?', 'translation' => 'Где примерочная?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I will pay by card', 'translation' => 'Я заплачу картой', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'This does not fit me', 'translation' => 'Это мне не подходит', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you have this in a larger size?', 'translation' => 'У вас есть это в большем размере?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'What is the total?', 'translation' => 'Какова общая сумма?', 'difficulty_level' => 2, 'topic' => 'Shopping'],

            // Feelings
            ['text' => 'I am happy', 'translation' => 'Я счастлив', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am sad', 'translation' => 'Мне грустно', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am tired', 'translation' => 'Я устал', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am excited', 'translation' => 'Я взволнован', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am worried', 'translation' => 'Я беспокоюсь', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am fine', 'translation' => 'У меня все хорошо', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am angry', 'translation' => 'Я зол', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am scared', 'translation' => 'Мне страшно', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I feel great', 'translation' => 'Я чувствую себя отлично', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am bored', 'translation' => 'Мне скучно', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am surprised', 'translation' => 'Я удивлён', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am nervous', 'translation' => 'Я нервничаю', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am proud of you', 'translation' => 'Я горжусь тобой', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I feel lonely', 'translation' => 'Я чувствую себя одиноким', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am confused', 'translation' => 'Я растерян', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I feel much better now', 'translation' => 'Сейчас мне намного лучше', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am disappointed', 'translation' => 'Я разочарован', 'difficulty_level' => 2, 'topic' => 'Feelings'],

            // Locations
            ['text' => 'Where is the bathroom?', 'translation' => 'Где туалет?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'I am at home', 'translation' => 'Я дома', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'The bank is here', 'translation' => 'Банк находится здесь', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Turn left', 'translation' => 'Поверните налево', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Turn right', 'translation' => 'Поверните направо', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Go straight', 'translation' => 'Идите прямо', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'It is near the park', 'translation' => 'Это рядом с парком', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Where is the hospital?', 'translation' => 'Где больница?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'The school is on the left', 'translation' => 'Школа слева', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'How far is it?', 'translation' => 'Как далеко это?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Is it far from here?', 'translation' => 'Это далеко отсюда?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Take the second street on the right', 'translation' => 'Поверните на вторую улицу направо', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'It is opposite the supermarket', 'translation' => 'Это напротив супермаркета', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'I am lost', 'translation' => 'Я заблудился', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Can you show me on the map?', 'translation' => 'Можете показать на карте?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'The post office is next to the library', 'translation' => 'Почта рядом с библиотекой', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'I am at the train station', 'translation' => 'Я на вокзале', 'difficulty_level' => 2, 'topic' => 'Locations'],
        ];
    }

    /**
     * A2 Level - Pre-Intermediate.
     */
    protected function getA2Phrases(): array
    {
        return [
            // Personal information
            ['text' => 'I live in a big city', 'translation' => 'Я живу в большом городе', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My hobby is reading books', 'translation' => 'Мое хобби - чтение книг', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I work as a teacher', 'translation' => 'Я работаю учителем', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I have been studying English for two years', 'translation' => 'Я изучаю английский два года', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My favorite color is green', 'translation' => 'Мой любимый цвет - зеленый', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I was born in nineteen ninety', 'translation' => 'Я родился в тысяча девятьсот девяностом', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I have two brothers and one sister', 'translation' => 'У меня два брата и одна сестра', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My parents live nearby', 'translation' => 'Мои родители живут рядом', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I enjoy spending time with my family', 'translation' => 'Мне нравится проводить время с семьей', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I speak three languages', 'translation' => 'Я говорю на трех языках', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I grew up in a small village', 'translation' => 'Я вырос в маленькой деревне', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My favorite subject at school was history', 'translation' => 'Мой любимый предмет в школе была история', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I have a pet dog named Max', 'translation' => 'У меня есть собака по имени Макс', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I am an only child', 'translation' => 'Я единственный ребенок', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I moved to this city five years ago', 'translation' => 'Я переехал в этот город пять лет назад', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My dream is to travel the world', 'translation' => 'Моя мечта - путешествовать по миру', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I am a morning person', 'translation' => 'Я жаворонок', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I prefer the countryside to the city', 'translation' => 'Я предпочитаю деревню городу', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'I have been living here since childhood', 'translation' => 'Я живу здесь с детства', 'difficulty_level' => 3, 'topic' => 'Personal Information'],
            ['text' => 'My favorite season is autumn', 'translation' => 'Моё любимое время года - осень', 'difficulty_level' => 3, 'topic' => 'Personal Information'],

            // Travel
            ['text' => 'Where is the train station?', 'translation' => 'Где находится вокзал?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I would like to book a room', 'translation' => 'Я хотел бы забронировать номер', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'How can I get to the airport?', 'translation' => 'Как мне добраться до аэропорта?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I am here on vacation', 'translation' => 'Я здесь в отпуске', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'Can you recommend a good restaurant?', 'translation' => 'Можете ли вы порекомендовать хороший ресторан?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'What time does the bus leave?', 'translation' => 'Во сколько отправляется автобус?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I need a taxi to the hotel', 'translation' => 'Мне нужно такси до отеля', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'Do I need a visa?', 'translation' => 'Мне нужна виза?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'My flight is at five p m', 'translation' => 'Мой рейс в пять вечера', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I lost my luggage', 'translation' => 'Я потерял свой багаж', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I would like a window seat', 'translation' => 'Я бы хотел место у окна', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'Is there a direct train to Paris?', 'translation' => 'Есть ли прямой поезд до Парижа?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'My passport expires next year', 'translation' => 'Мой паспорт истекает в следующем году', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I need to exchange money', 'translation' => 'Мне нужно обменять деньги', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'The hotel is fully booked', 'translation' => 'Отель полностью забронирован', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I would like to check out tomorrow', 'translation' => 'Я хотел бы выехать завтра', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'Can I have a map of the city?', 'translation' => 'Можно мне карту города?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'What are the must-see sights here?', 'translation' => 'Какие достопримечательности здесь обязательно посетить?', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'I missed my connecting flight', 'translation' => 'Я пропустил стыковочный рейс', 'difficulty_level' => 3, 'topic' => 'Travel'],
            ['text' => 'How long is the journey?', 'translation' => 'Сколько длится поездка?', 'difficulty_level' => 3, 'topic' => 'Travel'],

            // Health
            ['text' => 'I do not feel well', 'translation' => 'Я плохо себя чувствую', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a headache', 'translation' => 'У меня болит голова', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'Where is the nearest pharmacy?', 'translation' => 'Где ближайшая аптека?', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need to see a doctor', 'translation' => 'Мне нужно обратиться к врачу', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a fever', 'translation' => 'У меня температура', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'My stomach hurts', 'translation' => 'У меня болит живот', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I am allergic to peanuts', 'translation' => 'У меня аллергия на арахис', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need a prescription', 'translation' => 'Мне нужен рецепт', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a sore throat', 'translation' => 'У меня болит горло', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have been coughing all night', 'translation' => 'Я кашляю всю ночь', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need to take this medicine twice a day', 'translation' => 'Мне нужно принимать это лекарство дважды в день', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a dental appointment tomorrow', 'translation' => 'Завтра у меня приём у стоматолога', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I twisted my ankle', 'translation' => 'Я подвернул лодыжку', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need to rest for a few days', 'translation' => 'Мне нужно отдохнуть несколько дней', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'My back has been hurting for a week', 'translation' => 'У меня болит спина уже неделю', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I feel dizzy', 'translation' => 'У меня кружится голова', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have high blood pressure', 'translation' => 'У меня высокое кровяное давление', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need to drink more water', 'translation' => 'Мне нужно пить больше воды', 'difficulty_level' => 3, 'topic' => 'Health'],

            // Weather
            ['text' => 'It is raining today', 'translation' => 'Сегодня идет дождь', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The weather is nice', 'translation' => 'Погода хорошая', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It will be sunny tomorrow', 'translation' => 'Завтра будет солнечно', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'I love winter because I can ski', 'translation' => 'Я люблю зиму, потому что могу кататься на лыжах', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is very hot today', 'translation' => 'Сегодня очень жарко', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is snowing outside', 'translation' => 'На улице идет снег', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The temperature is below zero', 'translation' => 'Температура ниже нуля', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is quite windy', 'translation' => 'Довольно ветрено', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'There is a thunderstorm coming', 'translation' => 'Надвигается гроза', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is foggy this morning', 'translation' => 'Сегодня утром туман', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The forecast says it will rain all week', 'translation' => 'Прогноз говорит, что всю неделю будет дождь', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'I need to bring an umbrella', 'translation' => 'Мне нужно взять зонт', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The roads are icy', 'translation' => 'Дороги обледенели', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is a beautiful spring day', 'translation' => 'Это прекрасный весенний день', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The humidity is very high', 'translation' => 'Влажность очень высокая', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'There was a heavy snowfall last night', 'translation' => 'Прошлой ночью был сильный снегопад', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The sun is setting early these days', 'translation' => 'В эти дни солнце садится рано', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is getting colder every day', 'translation' => 'Каждый день становится холоднее', 'difficulty_level' => 3, 'topic' => 'Weather'],

            // Work
            ['text' => 'I start work at nine o clock', 'translation' => 'Я начинаю работу в девять часов', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I have a meeting this afternoon', 'translation' => 'У меня встреча сегодня днем', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'My boss is very kind', 'translation' => 'Мой начальник очень добрый', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I work from home twice a week', 'translation' => 'Я работаю из дома два раза в неделю', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I need to finish this project by Friday', 'translation' => 'Мне нужно закончить этот проект к пятнице', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'My colleagues are very helpful', 'translation' => 'Мои коллеги очень помогают', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I am looking for a new job', 'translation' => 'Я ищу новую работу', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I have been working here for five years', 'translation' => 'Я работаю здесь пять лет', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I got a promotion last month', 'translation' => 'В прошлом месяце меня повысили', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'My salary is paid at the end of the month', 'translation' => 'Моя зарплата выплачивается в конце месяца', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I have to work overtime this week', 'translation' => 'На этой неделе мне нужно работать сверхурочно', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I am on annual leave next week', 'translation' => 'На следующей неделе я в ежегодном отпуске', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'We have a deadline on Monday', 'translation' => 'У нас дедлайн в понедельник', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I submitted my report this morning', 'translation' => 'Я сдал отчёт этим утром', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I need to call a client', 'translation' => 'Мне нужно позвонить клиенту', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'Our team works very well together', 'translation' => 'Наша команда работает очень слаженно', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I applied for a job at a tech company', 'translation' => 'Я подал заявку на работу в технологическую компанию', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'The office is open from nine to six', 'translation' => 'Офис работает с девяти до шести', 'difficulty_level' => 3, 'topic' => 'Work'],

            // Education
            ['text' => 'I am studying computer science', 'translation' => 'Я изучаю информатику', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I have an exam next week', 'translation' => 'У меня экзамен на следующей неделе', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'This subject is difficult', 'translation' => 'Этот предмет сложный', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I need to study more', 'translation' => 'Мне нужно больше учиться', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'My teacher is excellent', 'translation' => 'Мой учитель превосходный', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I graduated from university last year', 'translation' => 'Я закончил университет в прошлом году', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I am writing my thesis', 'translation' => 'Я пишу дипломную работу', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'The library closes at eight', 'translation' => 'Библиотека закрывается в восемь', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I failed the test and need to retake it', 'translation' => 'Я провалил тест и должен его пересдать', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'Our professor assigned a lot of homework', 'translation' => 'Наш профессор задал много домашней работы', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I am taking an online course', 'translation' => 'Я прохожу онлайн-курс', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'The lecture starts at ten', 'translation' => 'Лекция начинается в десять', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I got a scholarship to study abroad', 'translation' => 'Я получил стипендию для учёбы за рубежом', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I am struggling with mathematics', 'translation' => 'Мне трудно даётся математика', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'We have a group project due next month', 'translation' => 'У нас групповой проект, который нужно сдать в следующем месяце', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I prefer practical lessons to theory', 'translation' => 'Я предпочитаю практические занятия теории', 'difficulty_level' => 3, 'topic' => 'Education'],

            // Hobbies
            ['text' => 'I enjoy playing football', 'translation' => 'Мне нравится играть в футбол', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I like to paint in my free time', 'translation' => 'Я люблю рисовать в свободное время', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I collect stamps', 'translation' => 'Я коллекционирую марки', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'My passion is photography', 'translation' => 'Моя страсть - фотография', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I go swimming every weekend', 'translation' => 'Я хожу плавать каждые выходные', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I have been playing the guitar since I was twelve', 'translation' => 'Я играю на гитаре с двенадцати лет', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I love hiking in the mountains', 'translation' => 'Я люблю ходить в горы', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I am learning to cook Italian food', 'translation' => 'Я учусь готовить итальянскую еду', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I watch films every Friday evening', 'translation' => 'Я смотрю фильмы каждую пятницу вечером', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I started doing yoga six months ago', 'translation' => 'Я начал заниматься йогой шесть месяцев назад', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I enjoy reading science fiction novels', 'translation' => 'Мне нравится читать научно-фантастические романы', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I go cycling at the park on Sundays', 'translation' => 'По воскресеньям я езжу на велосипеде в парке', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I am interested in astronomy', 'translation' => 'Я интересуюсь астрономией', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I play chess with my grandfather', 'translation' => 'Я играю в шахматы с дедушкой', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I have just started learning to knit', 'translation' => 'Я только что начал учиться вязать', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
        ];
    }

    /**
     * B1 Level - Intermediate.
     */
    protected function getB1Phrases(): array
    {
        return [
            // Opinions
            ['text' => 'I think that technology has changed our lives significantly', 'translation' => 'Я думаю, что технологии значительно изменили нашу жизнь', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'In my opinion, learning languages opens many doors', 'translation' => 'По моему мнению, изучение языков открывает много дверей', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I believe that everyone should have access to education', 'translation' => 'Я считаю, что каждый должен иметь доступ к образованию', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'As far as I am concerned, exercise is essential for good health', 'translation' => 'Насколько мне известно, физические упражнения необходимы для хорошего здоровья', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'From my perspective, reading is the best way to expand your mind', 'translation' => 'С моей точки зрения, чтение - лучший способ расширить кругозор', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I personally feel that environmental protection is crucial', 'translation' => 'Лично я считаю, что защита окружающей среды имеет решающее значение', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'It seems to me that people are becoming more aware of mental health', 'translation' => 'Мне кажется, что люди все больше осознают важность психического здоровья', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I strongly believe that kindness is more important than intelligence', 'translation' => 'Я твёрдо убеждён, что доброта важнее ума', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'To my mind, public transport should be free for everyone', 'translation' => 'По моему мнению, общественный транспорт должен быть бесплатным для всех', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I tend to think that social media does more harm than good', 'translation' => 'Я склонен думать, что социальные сети приносят больше вреда, чем пользы', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'In my view, the government should invest more in renewable energy', 'translation' => 'На мой взгляд, правительство должно больше инвестировать в возобновляемую энергию', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I am of the opinion that remote work increases productivity', 'translation' => 'Я придерживаюсь мнения, что удалённая работа повышает производительность', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'As I see it, the most important skill today is adaptability', 'translation' => 'Как я это вижу, самый важный навык сегодня — это адаптируемость', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I feel quite strongly that animals deserve better protection', 'translation' => 'Я твёрдо убеждён, что животные заслуживают лучшей защиты', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'Personally, I think that travelling broadens the mind', 'translation' => 'Лично я думаю, что путешествия расширяют кругозор', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'I would argue that diet is more important than exercise', 'translation' => 'Я бы утверждал, что диета важнее физических упражнений', 'difficulty_level' => 4, 'topic' => 'Opinions'],
            ['text' => 'It is my belief that honesty is always the best policy', 'translation' => 'Я верю, что честность всегда лучшая политика', 'difficulty_level' => 4, 'topic' => 'Opinions'],

            // Experiences
            ['text' => 'I have never been to Japan, but I would love to go', 'translation' => 'Я никогда не был в Японии, но хотел бы поехать', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'Last summer, I traveled to France and visited the Eiffel Tower', 'translation' => 'Прошлым летом я поехал во Францию и посетил Эйфелеву башню', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have been working on this project for three months', 'translation' => 'Я работаю над этим проектом три месяца', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'When I was a child, I used to play outside every day', 'translation' => 'Когда я был ребенком, я каждый день играл на улице', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'That was the most memorable experience of my life', 'translation' => 'Это был самый запоминающийся опыт в моей жизни', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I will never forget the day I graduated from college', 'translation' => 'Я никогда не забуду день, когда я закончил колледж', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'The first time I spoke in public, I was extremely nervous', 'translation' => 'Когда я впервые выступал публично, я был очень нервным', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I once got completely lost in a foreign city', 'translation' => 'Однажды я совершенно заблудился в чужом городе', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have tried many different cuisines during my travels', 'translation' => 'Во время путешествий я пробовал много разных кухонь', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I used to be afraid of heights, but I overcame it', 'translation' => 'Раньше я боялся высоты, но преодолел это', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have recently started learning how to drive', 'translation' => 'Я недавно начал учиться водить машину', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I met my best friend at university', 'translation' => 'Я познакомился со своим лучшим другом в университете', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have lived in three different countries', 'translation' => 'Я жил в трёх разных странах', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I once ran a marathon and it changed my life', 'translation' => 'Однажды я пробежал марафон, и это изменило мою жизнь', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'My first job was at a small local bakery', 'translation' => 'Моя первая работа была в небольшой местной пекарне', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have always wanted to learn how to play the piano', 'translation' => 'Я всегда хотел научиться играть на пианино', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I spent a year volunteering in Africa', 'translation' => 'Я провёл год, занимаясь волонтёрством в Африке', 'difficulty_level' => 4, 'topic' => 'Experiences'],

            // Plans and goals
            ['text' => 'I am planning to start my own business next year', 'translation' => 'Я планирую начать свой бизнес в следующем году', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'My goal is to become fluent in three languages', 'translation' => 'Моя цель - свободно владеть тремя языками', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I intend to finish this course before the end of the year', 'translation' => 'Я намерен закончить этот курс до конца года', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We are going to move to a new apartment in the spring', 'translation' => 'Мы собираемся переехать в новую квартиру весной', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'By this time next year, I will have completed my degree', 'translation' => 'К этому времени в следующем году я завершу свою степень', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am hoping to travel more in the future', 'translation' => 'Я надеюсь больше путешествовать в будущем', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We are aiming to reduce costs by twenty percent', 'translation' => 'Мы стремимся снизить затраты на двадцать процентов', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am thinking of applying for a master\'s degree', 'translation' => 'Я думаю о поступлении в магистратуру', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We plan to launch the product in the autumn', 'translation' => 'Мы планируем запустить продукт осенью', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I would like to buy a house within the next five years', 'translation' => 'Я хотел бы купить дом в течение следующих пяти лет', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am considering changing careers', 'translation' => 'Я рассматриваю смену карьеры', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'My long-term goal is to retire early', 'translation' => 'Моя долгосрочная цель — выйти на пенсию досрочно', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am saving money to go on a round-the-world trip', 'translation' => 'Я коплю деньги на кругосветное путешествие', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am going to take a gap year after university', 'translation' => 'После университета я собираюсь взять академический год', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We hope to expand our business internationally', 'translation' => 'Мы надеемся расширить бизнес на международный уровень', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I intend to read at least one book a month', 'translation' => 'Я намерен читать не менее одной книги в месяц', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am planning to adopt a healthier lifestyle', 'translation' => 'Я планирую перейти к более здоровому образу жизни', 'difficulty_level' => 4, 'topic' => 'Plans'],

            // Problems and solutions
            ['text' => 'The main problem is that we do not have enough resources', 'translation' => 'Основная проблема в том, что у нас недостаточно ресурсов', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We need to find a solution to this issue as soon as possible', 'translation' => 'Нам нужно найти решение этой проблемы как можно скорее', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'If we work together, we can overcome any challenge', 'translation' => 'Если мы будем работать вместе, мы сможем преодолеть любые трудности', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'One way to solve this would be to hire more staff', 'translation' => 'Один из способов решить это - нанять больше сотрудников', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'This situation requires immediate attention', 'translation' => 'Эта ситуация требует немедленного внимания', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'There must be a better way to handle this', 'translation' => 'Должен быть лучший способ справиться с этим', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'I am having trouble with my internet connection', 'translation' => 'У меня проблемы с интернет-соединением', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We ran out of budget before the project was finished', 'translation' => 'Бюджет закончился до завершения проекта', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'The best approach would be to address the root cause', 'translation' => 'Лучший подход — устранить первопричину', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We should consider all possible options before deciding', 'translation' => 'Прежде чем принять решение, нам следует рассмотреть все возможные варианты', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'I have been struggling with this problem for weeks', 'translation' => 'Я борюсь с этой проблемой уже несколько недель', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'A compromise might be the most practical solution', 'translation' => 'Компромисс может быть наиболее практичным решением', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We need to prevent this from happening again', 'translation' => 'Нам нужно предотвратить повторение этого', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'Let us brainstorm some ideas to tackle this issue', 'translation' => 'Давайте проведём мозговой штурм, чтобы решить эту проблему', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'The problem is more complex than it first appeared', 'translation' => 'Проблема сложнее, чем казалась на первый взгляд', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We should delegate some tasks to reduce the workload', 'translation' => 'Нам следует делегировать часть задач, чтобы снизить нагрузку', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'I think we should ask for expert advice', 'translation' => 'Я думаю, нам следует обратиться за советом к эксперту', 'difficulty_level' => 4, 'topic' => 'Problems'],

            // Social situations
            ['text' => 'Would you mind if I opened the window?', 'translation' => 'Вы не возражаете, если я открою окно?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Could you please help me with this task?', 'translation' => 'Не могли бы вы помочь мне с этим заданием?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I would appreciate it if you could send me the details', 'translation' => 'Я был бы признателен, если бы вы могли прислать мне детали', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Let me know if you need anything else', 'translation' => 'Дайте мне знать, если вам что-то еще нужно', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I was wondering if you could give me some advice', 'translation' => 'Мне было интересно, не могли бы вы дать мне совет', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Thank you for taking the time to meet with me', 'translation' => 'Спасибо, что нашли время встретиться со мной', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I completely understand your point of view', 'translation' => 'Я полностью понимаю вашу точку зрения', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I am afraid I cannot make it to the party', 'translation' => 'Боюсь, что не смогу прийти на вечеринку', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Could we reschedule our meeting to Thursday?', 'translation' => 'Не могли бы мы перенести встречу на четверг?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I hope I am not disturbing you', 'translation' => 'Надеюсь, я вас не беспокою', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I really enjoyed spending time with you', 'translation' => 'Мне очень понравилось проводить с вами время', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Do you mind if I join you?', 'translation' => 'Вы не против, если я присоединюсь к вам?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I owe you a big favour', 'translation' => 'Я вам очень обязан', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Please feel free to ask me anything', 'translation' => 'Пожалуйста, не стесняйтесь задавать мне любые вопросы', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I am sorry for the misunderstanding', 'translation' => 'Извините за недоразумение', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Shall we go somewhere quieter to talk?', 'translation' => 'Может, пойдём куда-нибудь потише, чтобы поговорить?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I really appreciate your support', 'translation' => 'Я очень ценю вашу поддержку', 'difficulty_level' => 4, 'topic' => 'Social'],

            // Technology
            ['text' => 'I need to update my computer software', 'translation' => 'Мне нужно обновить программное обеспечение компьютера', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Social media has changed the way we communicate', 'translation' => 'Социальные сети изменили способ общения', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I cannot live without my smartphone', 'translation' => 'Я не могу жить без смартфона', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Online shopping is very convenient', 'translation' => 'Интернет-магазины очень удобны', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I prefer video calls to phone calls', 'translation' => 'Я предпочитаю видеозвонки телефонным звонкам', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'My laptop battery dies very quickly', 'translation' => 'Аккумулятор моего ноутбука разряжается очень быстро', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I use a password manager to keep my accounts safe', 'translation' => 'Я использую менеджер паролей для защиты своих аккаунтов', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'The app crashed and I lost all my data', 'translation' => 'Приложение вылетело, и я потерял все данные', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I back up my files to the cloud every week', 'translation' => 'Я делаю резервную копию файлов в облако каждую неделю', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Artificial intelligence is transforming many industries', 'translation' => 'Искусственный интеллект трансформирует многие отрасли', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I spend too much time on my phone', 'translation' => 'Я трачу слишком много времени на телефон', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'The internet connection is very slow today', 'translation' => 'Сегодня интернет-соединение очень медленное', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I have set up two-factor authentication on all my accounts', 'translation' => 'Я настроил двухфакторную аутентификацию на всех своих аккаунтах', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Streaming services have replaced traditional television', 'translation' => 'Стриминговые сервисы заменили традиционное телевидение', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I need to restart the router to fix the connection', 'translation' => 'Мне нужно перезагрузить роутер, чтобы исправить соединение', 'difficulty_level' => 4, 'topic' => 'Technology'],

            // Relationships
            ['text' => 'I have known my best friend since childhood', 'translation' => 'Я знаю своего лучшего друга с детства', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'Communication is key in any relationship', 'translation' => 'Общение - ключ к любым отношениям', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We should spend more quality time together', 'translation' => 'Нам следует проводить больше качественного времени вместе', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'It is important to respect each other s opinions', 'translation' => 'Важно уважать мнения друг друга', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We had a disagreement, but we resolved it quickly', 'translation' => 'У нас было разногласие, но мы быстро его разрешили', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'I try to be there for my friends when they need me', 'translation' => 'Я стараюсь быть рядом с друзьями, когда им это нужно', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'Trust is the foundation of any strong relationship', 'translation' => 'Доверие — основа любых крепких отношений', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We have been together for three years now', 'translation' => 'Мы вместе уже три года', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'I find it hard to make new friends in a new city', 'translation' => 'Мне трудно заводить новых друзей в новом городе', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We keep in touch even though we live far apart', 'translation' => 'Мы поддерживаем связь, несмотря на то что живём далеко друг от друга', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'I think it is important to set boundaries in relationships', 'translation' => 'Я думаю, важно устанавливать границы в отношениях', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'My parents have been married for thirty years', 'translation' => 'Мои родители женаты тридцать лет', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We met through a mutual friend', 'translation' => 'Мы познакомились через общего друга', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'I always try to listen before giving advice', 'translation' => 'Я всегда стараюсь выслушать, прежде чем давать советы', 'difficulty_level' => 4, 'topic' => 'Relationships'],
        ];
    }

    /**
     * B2 Level - Upper-Intermediate.
     */
    protected function getB2Phrases(): array
    {
        return [
            // Complex opinions
            ['text' => 'Although some people argue that social media has negative effects, I believe it has revolutionized communication', 'translation' => 'Хотя некоторые утверждают, что социальные сети имеют негативные последствия, я считаю, что они произвели революцию в общении', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The extent to which technology influences our daily lives is remarkable', 'translation' => 'Степень влияния технологий на нашу повседневную жизнь поразительна', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'It is worth noting that environmental issues require immediate attention', 'translation' => 'Стоит отметить, что экологические проблемы требуют немедленного внимания', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'There is no denying that education plays a crucial role in personal development', 'translation' => 'Нельзя отрицать, что образование играет решающую роль в личностном развитии', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'Despite the challenges, I remain optimistic about the future', 'translation' => 'Несмотря на проблемы, я остаюсь оптимистично настроенным относительно будущего', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The prevailing view is that renewable energy is the way forward', 'translation' => 'Преобладающее мнение состоит в том, что возобновляемая энергия - путь вперед', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'One cannot underestimate the impact of early childhood education on lifelong outcomes', 'translation' => 'Нельзя недооценивать влияние дошкольного образования на результаты всей жизни', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The evidence strongly suggests that a plant-based diet has significant health benefits', 'translation' => 'Данные убедительно свидетельствуют о том, что растительная диета имеет значительные преимущества для здоровья', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'It is debatable whether stricter laws would actually reduce crime rates', 'translation' => 'Спорно, действительно ли более строгие законы снизят уровень преступности', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'I find it hard to accept the argument that economic growth always leads to improved wellbeing', 'translation' => 'Мне трудно принять аргумент о том, что экономический рост всегда ведёт к улучшению благосостояния', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The notion that success is purely a result of hard work overlooks the role of privilege', 'translation' => 'Представление о том, что успех — это исключительно результат упорного труда, игнорирует роль привилегий', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'Contrary to popular belief, multitasking often reduces overall productivity', 'translation' => 'Вопреки распространённому мнению, многозадачность часто снижает общую производительность', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'I would contend that the benefits of globalisation outweigh its drawbacks', 'translation' => 'Я бы утверждал, что преимущества глобализации перевешивают её недостатки', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'It goes without saying that access to clean water is a fundamental human right', 'translation' => 'Само собой разумеется, что доступ к чистой воде является основным правом человека', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The assumption that younger generations are less hardworking is largely unfounded', 'translation' => 'Предположение о том, что молодые поколения менее трудолюбивы, в значительной мере необоснованно', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'While I acknowledge the merits of this approach, I have reservations about its long-term viability', 'translation' => 'Признавая достоинства этого подхода, я сомневаюсь в его долгосрочной жизнеспособности', 'difficulty_level' => 5, 'topic' => 'Opinions'],
            ['text' => 'The relationship between poverty and crime is far more nuanced than it is often portrayed', 'translation' => 'Связь между бедностью и преступностью гораздо сложнее, чем её обычно изображают', 'difficulty_level' => 5, 'topic' => 'Opinions'],

            // Professional contexts
            ['text' => 'Our team has successfully implemented a new strategy that increased productivity by thirty percent', 'translation' => 'Наша команда успешно внедрила новую стратегию, которая увеличила производительность на тридцать процентов', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The company is committed to fostering innovation and encouraging creative thinking', 'translation' => 'Компания стремится развивать инновации и поощрять творческое мышление', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We need to reassess our approach and consider alternative solutions', 'translation' => 'Нам нужно пересмотреть наш подход и рассмотреть альтернативные решения', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'Effective leadership requires both vision and the ability to execute', 'translation' => 'Эффективное лидерство требует как видения, так и способности к исполнению', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The quarterly results exceeded our expectations significantly', 'translation' => 'Квартальные результаты значительно превысили наши ожидания', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We must leverage our strengths while addressing our weaknesses', 'translation' => 'Мы должны использовать наши сильные стороны, устраняя слабые', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The merger will create synergies that benefit both organizations', 'translation' => 'Слияние создаст синергию, которая принесет пользу обеим организациям', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We are in the process of restructuring our supply chain to improve efficiency', 'translation' => 'Мы находимся в процессе реструктуризации цепочки поставок для повышения эффективности', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The board has approved the budget for the upcoming fiscal year', 'translation' => 'Совет директоров утвердил бюджет на предстоящий финансовый год', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We need to align our marketing strategy with the overall business objectives', 'translation' => 'Нам необходимо согласовать маркетинговую стратегию с общими бизнес-целями', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The client has requested several amendments to the original proposal', 'translation' => 'Клиент запросил несколько изменений в первоначальном предложении', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'Our competitive advantage lies in our ability to adapt quickly to market changes', 'translation' => 'Наше конкурентное преимущество заключается в способности быстро адаптироваться к изменениям рынка', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'I would like to propose a new initiative to improve employee engagement', 'translation' => 'Я хотел бы предложить новую инициативу по повышению вовлечённости сотрудников', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The due diligence process revealed some concerns that need to be addressed', 'translation' => 'Процесс комплексной проверки выявил ряд проблем, которые необходимо решить', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We have exceeded our key performance indicators for the third consecutive quarter', 'translation' => 'Мы превысили ключевые показатели эффективности третий квартал подряд', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The pilot programme has yielded promising results and we plan to scale it up', 'translation' => 'Пилотная программа дала многообещающие результаты, и мы планируем её расширить', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'Transparent communication between management and employees is essential for a healthy workplace', 'translation' => 'Прозрачная коммуникация между руководством и сотрудниками необходима для здоровой рабочей среды', 'difficulty_level' => 5, 'topic' => 'Professional'],

            // Abstract concepts
            ['text' => 'The relationship between culture and identity is complex and multifaceted', 'translation' => 'Связь между культурой и идентичностью сложна и многогранна', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Understanding different perspectives can broaden your worldview significantly', 'translation' => 'Понимание различных точек зрения может значительно расширить ваше мировоззрение', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The concept of sustainability has become increasingly important in modern society', 'translation' => 'Концепция устойчивого развития становится все более важной в современном обществе', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Human nature is characterized by both altruism and self-interest', 'translation' => 'Человеческая природа характеризуется как альтруизмом, так и корыстью', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The notion of progress is relative and culturally dependent', 'translation' => 'Понятие прогресса относительно и культурно обусловлено', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Democracy thrives on active citizen participation and transparency', 'translation' => 'Демократия процветает благодаря активному участию граждан и прозрачности', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The concept of justice varies significantly across different cultures and legal systems', 'translation' => 'Понятие справедливости значительно варьируется в разных культурах и правовых системах', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Freedom of expression must be balanced against the need to prevent harm', 'translation' => 'Свобода слова должна быть сбалансирована с необходимостью предотвращения вреда', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The idea of a universal moral code is both appealing and deeply problematic', 'translation' => 'Идея универсального морального кодекса одновременно привлекательна и глубоко проблематична', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Power dynamics shape every aspect of social interaction', 'translation' => 'Динамика власти формирует каждый аспект социального взаимодействия', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The boundaries between public and private life are increasingly blurred in the digital age', 'translation' => 'Границы между общественной и частной жизнью всё больше размываются в цифровую эпоху', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Collective memory plays a fundamental role in shaping national identity', 'translation' => 'Коллективная память играет фундаментальную роль в формировании национальной идентичности', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The tension between tradition and modernity is a recurring theme in many societies', 'translation' => 'Напряжённость между традицией и современностью — повторяющаяся тема во многих обществах', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Language both reflects and shapes the way we perceive reality', 'translation' => 'Язык одновременно отражает и формирует то, как мы воспринимаем реальность', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The pursuit of happiness is a deeply personal and culturally influenced endeavour', 'translation' => 'Стремление к счастью — это глубоко личное и культурно обусловленное занятие', 'difficulty_level' => 5, 'topic' => 'Abstract'],

            // Debates and arguments
            ['text' => 'On the one hand, globalization has brought economic benefits, but on the other hand, it has led to cultural homogenization', 'translation' => 'С одной стороны, глобализация принесла экономические выгоды, но с другой стороны, она привела к культурной гомогенизации', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'There is considerable debate about whether artificial intelligence will replace human workers', 'translation' => 'Ведутся значительные дебаты о том, заменит ли искусственный интеллект работников-людей', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'While I understand your point of view, I would argue that the evidence suggests otherwise', 'translation' => 'Хотя я понимаю вашу точку зрения, я бы утверждал, что доказательства говорят об обратном', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The benefits of free trade must be weighed against its potential drawbacks', 'translation' => 'Преимущества свободной торговли должны быть взвешены против ее потенциальных недостатков', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'It could be argued that privacy concerns have not kept pace with technological advancement', 'translation' => 'Можно утверждать, что проблемы конфиденциальности не успевают за технологическим прогрессом', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The debate over nuclear energy is far from settled, with compelling arguments on both sides', 'translation' => 'Дискуссия об ядерной энергетике далека от завершения, с убедительными аргументами с обеих сторон', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'Critics argue that austerity measures disproportionately affect the most vulnerable members of society', 'translation' => 'Критики утверждают, что меры жёсткой экономии непропорционально затрагивают наиболее уязвимых членов общества', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'Proponents of universal basic income claim it would reduce poverty and stimulate the economy', 'translation' => 'Сторонники безусловного базового дохода утверждают, что он сократит бедность и стимулирует экономику', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The question of whether social media companies should be regulated more strictly is highly contentious', 'translation' => 'Вопрос о том, следует ли более строго регулировать компании социальных сетей, весьма спорен', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'Some economists contend that immigration has a net positive effect on the host country\'s economy', 'translation' => 'Некоторые экономисты утверждают, что иммиграция оказывает чистое положительное воздействие на экономику принимающей страны', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The argument that space exploration is a waste of resources ignores its numerous scientific benefits', 'translation' => 'Аргумент о том, что освоение космоса является пустой тратой ресурсов, игнорирует его многочисленные научные преимущества', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'Both sides of the debate make valid points, but the truth likely lies somewhere in the middle', 'translation' => 'Обе стороны дискуссии приводят обоснованные аргументы, но истина, вероятно, находится где-то посередине', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The case for stricter gun control is supported by a substantial body of research', 'translation' => 'Аргументы в пользу более строгого контроля над оружием подкреплены значительным массивом исследований', 'difficulty_level' => 5, 'topic' => 'Debates'],

            // Hypothetical situations
            ['text' => 'If I had known about the traffic, I would have left earlier', 'translation' => 'Если бы я знал о пробках, я бы уехал раньше', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Had we been more prepared, the outcome might have been different', 'translation' => 'Будь мы более подготовлены, результат мог бы быть другим', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'I wish I could speak more languages fluently', 'translation' => 'Жаль, что я не могу свободно говорить на большем количестве языков', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Suppose you won the lottery, what would you do with the money?', 'translation' => 'Предположим, вы выиграли в лотерею, что бы вы сделали с деньгами?', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'If I were in your position, I would consider all available options', 'translation' => 'Если бы я был на вашем месте, я бы рассмотрел все доступные варианты', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'If governments had acted sooner on climate change, we would be in a better position today', 'translation' => 'Если бы правительства раньше приняли меры по изменению климата, сегодня мы были бы в лучшем положении', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Imagine a world where everyone had access to quality healthcare', 'translation' => 'Представьте мир, в котором у каждого есть доступ к качественному здравоохранению', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'What would happen if we abolished all forms of taxation?', 'translation' => 'Что бы произошло, если бы мы отменили все виды налогообложения?', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'If I could go back in time, I would have studied medicine', 'translation' => 'Если бы я мог вернуться в прошлое, я бы выучился на врача', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Were the company to relocate, many employees would face difficult choices', 'translation' => 'Если бы компания переехала, многие сотрудники столкнулись бы с трудным выбором', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'If only we had invested in renewable energy a decade ago', 'translation' => 'Если бы только мы инвестировали в возобновляемую энергию десять лет назад', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Assuming the project is approved, we would need to hire additional staff', 'translation' => 'Предполагая, что проект будет одобрен, нам потребуется нанять дополнительный персонал', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'What if we approached the problem from a completely different angle?', 'translation' => 'Что если бы мы подошли к проблеме с совершенно другой стороны?', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],

            // Culture and society
            ['text' => 'Cultural diversity enriches our societies in countless ways', 'translation' => 'Культурное разнообразие обогащает наши общества бесчисленными способами', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The arts play a vital role in reflecting and shaping social values', 'translation' => 'Искусство играет жизненно важную роль в отражении и формировании социальных ценностей', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Traditions should be preserved while embracing modernity', 'translation' => 'Традиции должны сохраняться при принятии современности', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The gap between generations can be bridged through dialogue and understanding', 'translation' => 'Разрыв между поколениями можно преодолеть через диалог и понимание', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The influence of Western culture on traditional societies raises important ethical questions', 'translation' => 'Влияние западной культуры на традиционные общества поднимает важные этические вопросы', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Festivals and public celebrations are an important expression of collective identity', 'translation' => 'Фестивали и публичные торжества являются важным выражением коллективной идентичности', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The digital revolution has fundamentally altered how culture is created and consumed', 'translation' => 'Цифровая революция коренным образом изменила то, как создаётся и потребляется культура', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Multiculturalism presents both opportunities and challenges for social cohesion', 'translation' => 'Мультикультурализм представляет как возможности, так и проблемы для социальной сплочённости', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The role of religion in public life remains a deeply divisive issue in many countries', 'translation' => 'Роль религии в общественной жизни остаётся глубоко спорным вопросом во многих странах', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Popular culture often reflects and amplifies prevailing social anxieties', 'translation' => 'Популярная культура часто отражает и усиливает господствующие социальные тревоги', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The preservation of endangered languages is crucial for maintaining cultural heritage', 'translation' => 'Сохранение исчезающих языков имеет решающее значение для поддержания культурного наследия', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Street art has evolved from an act of rebellion to a celebrated form of cultural expression', 'translation' => 'Уличное искусство превратилось из акта протеста в признанную форму культурного самовыражения', 'difficulty_level' => 5, 'topic' => 'Culture'],

            // Economics
            ['text' => 'Economic inequality has reached unprecedented levels in many countries', 'translation' => 'Экономическое неравенство достигло беспрецедентного уровня во многих странах', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Market forces alone cannot address all societal needs', 'translation' => 'Одни только рыночные силы не могут решить все потребности общества', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Investment in education yields long-term economic benefits', 'translation' => 'Инвестиции в образование приносят долгосрочные экономические выгоды', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'The global financial crisis of 2008 exposed fundamental weaknesses in the banking sector', 'translation' => 'Мировой финансовый кризис 2008 года обнажил фундаментальные слабости банковского сектора', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Automation is increasingly displacing low-skilled workers in manufacturing industries', 'translation' => 'Автоматизация всё больше вытесняет низкоквалифицированных работников в обрабатывающей промышленности', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'The gig economy offers flexibility but often lacks adequate worker protections', 'translation' => 'Экономика временной занятости предлагает гибкость, но часто не обеспечивает надлежащей защиты работников', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Inflation erodes purchasing power and disproportionately affects lower-income households', 'translation' => 'Инфляция подрывает покупательную способность и непропорционально затрагивает домохозяйства с низкими доходами', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Foreign direct investment can stimulate economic growth in developing nations', 'translation' => 'Прямые иностранные инвестиции могут стимулировать экономический рост в развивающихся странах', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'The housing market has become increasingly unaffordable for first-time buyers', 'translation' => 'Рынок жилья становится всё менее доступным для покупателей, приобретающих жильё впервые', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Supply chain disruptions have highlighted the risks of over-reliance on a single supplier', 'translation' => 'Перебои в цепочке поставок обнажили риски чрезмерной зависимости от одного поставщика', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'The circular economy model aims to eliminate waste and maximise resource efficiency', 'translation' => 'Модель циклической экономики направлена на устранение отходов и максимизацию эффективности использования ресурсов', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Central banks use interest rates as a primary tool to control inflation', 'translation' => 'Центральные банки используют процентные ставки в качестве основного инструмента для контроля инфляции', 'difficulty_level' => 5, 'topic' => 'Economics'],
        ];
    }

    /**
     * C1 Level - Advanced.
     */
    protected function getC1Phrases(): array
    {
        return [
            // Sophisticated discourse
            ['text' => 'The ramifications of climate change extend far beyond what most people perceive, affecting everything from geopolitical stability to economic structures', 'translation' => 'Последствия изменения климата выходят далеко за рамки того, что воспринимает большинство людей, влияя на все: от геополитической стабильности до экономических структур', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'Notwithstanding the challenges we face, there remains considerable scope for innovation and improvement', 'translation' => 'Несмотря на стоящие перед нами проблемы, остается значительный потенциал для инноваций и улучшений', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The phenomenon can be attributed to a confluence of socioeconomic factors that have been building over decades', 'translation' => 'Это явление можно объяснить слиянием социально-экономических факторов, которые накапливались в течение десятилетий', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The prevalence of misinformation poses a significant threat to democratic institutions', 'translation' => 'Распространенность дезинформации представляет значительную угрозу для демократических институтов', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'Technological disruption has fundamentally altered the landscape of traditional industries', 'translation' => 'Технологические потрясения фундаментально изменили ландшафт традиционных отраслей', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The interplay between individual agency and structural constraints remains a central concern in sociological inquiry', 'translation' => 'Взаимодействие между индивидуальной свободой действий и структурными ограничениями остаётся центральной проблемой социологических исследований', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The erosion of trust in public institutions is one of the defining challenges of our era', 'translation' => 'Подрыв доверия к общественным институтам — одна из определяющих проблем нашей эпохи', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The proliferation of digital platforms has fundamentally reconfigured the public sphere', 'translation' => 'Распространение цифровых платформ коренным образом изменило публичную сферу', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'Geopolitical tensions are increasingly manifesting in economic and technological competition', 'translation' => 'Геополитическая напряжённость всё больше проявляется в экономической и технологической конкуренции', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The normative frameworks that once governed international relations are under considerable strain', 'translation' => 'Нормативные рамки, которые некогда регулировали международные отношения, испытывают значительное давление', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The commodification of personal data raises profound questions about autonomy and consent', 'translation' => 'Коммодификация персональных данных поднимает глубокие вопросы об автономии и согласии', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'Institutional inertia often impedes the implementation of necessary reforms', 'translation' => 'Институциональная инертность нередко препятствует проведению необходимых реформ', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The dialectic between security and liberty is one that societies must continually renegotiate', 'translation' => 'Диалектика между безопасностью и свободой — это то, что общества должны постоянно пересматривать', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'Cognitive biases systematically distort our perception of risk and probability', 'translation' => 'Когнитивные предубеждения систематически искажают наше восприятие риска и вероятности', 'difficulty_level' => 6, 'topic' => 'Discourse'],
            ['text' => 'The accelerating pace of technological change outstrips our capacity for ethical reflection', 'translation' => 'Ускоряющийся темп технологических изменений опережает нашу способность к этическому осмыслению', 'difficulty_level' => 6, 'topic' => 'Discourse'],

            // Academic language
            ['text' => 'The study elucidates the intricate mechanisms underlying cognitive development in early childhood', 'translation' => 'Исследование проясняет сложные механизмы, лежащие в основе когнитивного развития в раннем детстве', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'This theory postulates that human behavior is fundamentally shaped by environmental and genetic factors', 'translation' => 'Эта теория постулирует, что поведение человека фундаментально формируется факторами окружающей среды и генетики', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The research methodology employed was designed to minimize bias and ensure reproducibility', 'translation' => 'Использованная методология исследования была разработана для минимизации предвзятости и обеспечения воспроизводимости', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The findings challenge conventional wisdom and warrant further investigation', 'translation' => 'Результаты бросают вызов общепринятой мудрости и оправдывают дальнейшее исследование', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'Empirical evidence substantiates the hypothesis that has been proposed', 'translation' => 'Эмпирические данные подтверждают предложенную гипотезу', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The longitudinal study tracked participants over a period of twenty years', 'translation' => 'Лонгитюдное исследование отслеживало участников на протяжении двадцати лет', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The meta-analysis synthesises data from over fifty independent studies', 'translation' => 'Мета-анализ синтезирует данные более чем из пятидесяти независимых исследований', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The theoretical framework draws on both structuralist and post-structuralist traditions', 'translation' => 'Теоретическая основа опирается как на структуралистские, так и на постструктуралистские традиции', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The sample size was insufficient to draw statistically significant conclusions', 'translation' => 'Размер выборки был недостаточен для формулировки статистически значимых выводов', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The peer review process is essential for maintaining the integrity of scientific knowledge', 'translation' => 'Процесс рецензирования коллегами необходим для поддержания целостности научных знаний', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The interdisciplinary approach allows for a more holistic understanding of the phenomenon', 'translation' => 'Междисциплинарный подход позволяет более целостно понять явление', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'Replication studies are crucial for validating the original findings', 'translation' => 'Репликационные исследования имеют решающее значение для подтверждения исходных результатов', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The qualitative data was analysed using thematic coding', 'translation' => 'Качественные данные были проанализированы с использованием тематического кодирования', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The study acknowledges several limitations that may affect the generalisability of its findings', 'translation' => 'Исследование признаёт ряд ограничений, которые могут повлиять на обобщаемость его результатов', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The construct validity of the instrument used in the study has been questioned by several scholars', 'translation' => 'Конструктная валидность инструмента, использованного в исследовании, была поставлена под сомнение рядом учёных', 'difficulty_level' => 6, 'topic' => 'Academic'],

            // Nuanced arguments
            ['text' => 'While it would be overly simplistic to attribute the crisis to a single cause, the evidence overwhelmingly points to systemic failures', 'translation' => 'Хотя было бы чрезмерно упрощенным приписывать кризис одной причине, доказательства подавляюще указывают на системные сбои', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The dichotomy between individual freedom and collective responsibility lies at the heart of many contemporary political debates', 'translation' => 'Дихотомия между индивидуальной свободой и коллективной ответственностью лежит в основе многих современных политических дебатов', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'It is imperative that we distinguish between correlation and causation when interpreting statistical data', 'translation' => 'Крайне важно различать корреляцию и причинно-следственную связь при интерпретации статистических данных', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The discourse surrounding this issue has become increasingly polarized and lacks nuance', 'translation' => 'Дискурс вокруг этого вопроса становится все более поляризованным и лишен нюансов', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The argument rests on a false dichotomy that ignores the spectrum of available options', 'translation' => 'Аргумент основан на ложной дихотомии, которая игнорирует спектр доступных вариантов', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'One must be careful not to conflate the descriptive with the normative in this analysis', 'translation' => 'Необходимо быть осторожным, чтобы не смешивать описательное с нормативным в этом анализе', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The logical inconsistency at the heart of this position undermines its overall credibility', 'translation' => 'Логическая непоследовательность в основе этой позиции подрывает её общую достоверность', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'Acknowledging the complexity of the issue does not preclude taking a clear and principled stance', 'translation' => 'Признание сложности вопроса не исключает занятия чёткой и принципиальной позиции', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The counterargument, while superficially persuasive, fails to account for several crucial variables', 'translation' => 'Контраргумент, хотя и поверхностно убедительный, не учитывает несколько ключевых переменных', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The evidence is equivocal and does not permit us to draw definitive conclusions at this stage', 'translation' => 'Доказательства неоднозначны и не позволяют нам делать окончательные выводы на данном этапе', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'A more nuanced reading of the data reveals a pattern that contradicts the initial interpretation', 'translation' => 'Более тонкое прочтение данных выявляет закономерность, противоречащую первоначальной интерпретации', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The slippery slope argument, though rhetorically effective, is rarely supported by empirical evidence', 'translation' => 'Аргумент о скользком склоне, хотя и риторически эффективный, редко подкреплён эмпирическими данными', 'difficulty_level' => 6, 'topic' => 'Arguments'],

            // Professional expertise
            ['text' => 'Our comprehensive analysis revealed several previously unidentified vulnerabilities in the system architecture', 'translation' => 'Наш всесторонний анализ выявил несколько ранее неопознанных уязвимостей в архитектуре системы', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The implementation of this framework necessitates a paradigm shift in how we approach organizational structure', 'translation' => 'Внедрение этой структуры требует смены парадигмы в подходе к организационной структуре', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'Strategic alignment between different departments is crucial for achieving long-term organizational objectives', 'translation' => 'Стратегическое согласование между различными отделами имеет решающее значение для достижения долгосрочных организационных целей', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The stakeholders have expressed concerns regarding the sustainability of the current business model', 'translation' => 'Заинтересованные стороны выразили обеспокоенность относительно устойчивости текущей бизнес-модели', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The risk assessment identified several critical failure points that require immediate mitigation', 'translation' => 'Оценка рисков выявила несколько критических точек отказа, требующих немедленного снижения', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The organisation must cultivate a culture of continuous improvement to remain competitive', 'translation' => 'Организация должна культивировать культуру непрерывного совершенствования, чтобы оставаться конкурентоспособной', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The proposed restructuring will require significant investment but is expected to yield substantial returns', 'translation' => 'Предлагаемая реструктуризация потребует значительных инвестиций, но, как ожидается, принесёт существенную отдачу', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'Regulatory compliance is not merely a legal obligation but a cornerstone of corporate integrity', 'translation' => 'Соблюдение нормативных требований — это не просто юридическое обязательство, но и краеугольный камень корпоративной честности', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The talent acquisition strategy must be realigned to address the evolving skills landscape', 'translation' => 'Стратегия привлечения талантов должна быть перестроена с учётом меняющегося ландшафта навыков', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The organisation\'s resilience depends on its ability to anticipate and adapt to disruptive forces', 'translation' => 'Устойчивость организации зависит от её способности предвидеть разрушительные силы и адаптироваться к ним', 'difficulty_level' => 6, 'topic' => 'Professional'],
        ];
    }

    /**
     * C2 Level - Proficient.
     */
    protected function getC2Phrases(): array
    {
        return [
            // Philosophy
            ['text' => 'The juxtaposition of these seemingly disparate elements serves to underscore the fundamental paradox inherent in the human condition', 'translation' => 'Сопоставление этих, казалось бы, разрозненных элементов служит для подчеркивания фундаментального парадокса, присущего человеческому состоянию', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Notwithstanding the pervasive skepticism, the empirical evidence lends credence to the hypothesis that consciousness may transcend purely materialistic explanations', 'translation' => 'Несмотря на всеобщий скептицизм, эмпирические доказательства придают правдоподобность гипотезе о том, что сознание может выходить за рамки чисто материалистических объяснений', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The perennial tension between determinism and free will continues to animate philosophical debate', 'translation' => 'Извечное противоречие между детерминизмом и свободой воли продолжает оживлять философские дебаты', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Phenomenological inquiry seeks to describe the structures of experience as they present themselves to consciousness', 'translation' => 'Феноменологическое исследование стремится описать структуры опыта в том виде, в каком они предстают перед сознанием', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The ontological status of mathematical objects remains one of the most contested questions in the philosophy of mathematics', 'translation' => 'Онтологический статус математических объектов остаётся одним из наиболее спорных вопросов в философии математики', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Utilitarianism, in its classical formulation, reduces all moral questions to a calculus of pleasure and pain', 'translation' => 'Утилитаризм в своей классической формулировке сводит все моральные вопросы к исчислению удовольствия и боли', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The Kantian categorical imperative demands that we act only according to maxims we could will to be universal laws', 'translation' => 'Кантовский категорический императив требует, чтобы мы действовали только в соответствии с максимами, которые мы могли бы желать сделать всеобщими законами', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Existentialism posits that existence precedes essence, placing radical responsibility on the individual', 'translation' => 'Экзистенциализм постулирует, что существование предшествует сущности, возлагая радикальную ответственность на индивида', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The problem of other minds illustrates the profound epistemological limits of our knowledge of the external world', 'translation' => 'Проблема чужих сознаний иллюстрирует глубокие эпистемологические пределы нашего знания о внешнем мире', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Postmodern thought challenges the Enlightenment belief in universal reason and objective truth', 'translation' => 'Постмодернистская мысль бросает вызов просветительской вере в универсальный разум и объективную истину', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The ship of Theseus paradox raises profound questions about identity, continuity, and the nature of objects over time', 'translation' => 'Парадокс корабля Тесея поднимает глубокие вопросы об идентичности, непрерывности и природе объектов во времени', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Hegel\'s dialectical method conceives of history as the progressive self-realisation of Absolute Spirit', 'translation' => 'Диалектический метод Гегеля рассматривает историю как прогрессивное самоосуществление Абсолютного Духа', 'difficulty_level' => 7, 'topic' => 'Philosophy'],

            // Science
            ['text' => 'The epistemological implications of quantum mechanics continue to perplex even the most erudite scholars in the field', 'translation' => 'Эпистемологические последствия квантовой механики продолжают озадачивать даже самых эрудированных ученых в этой области', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The principle of falsifiability, as articulated by Popper, remains a cornerstone of the philosophy of science', 'translation' => 'Принцип фальсифицируемости, сформулированный Поппером, остаётся краеугольным камнем философии науки', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The emergence of complex systems from simple rules challenges reductionist explanations of natural phenomena', 'translation' => 'Возникновение сложных систем из простых правил бросает вызов редукционистским объяснениям природных явлений', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The anthropic principle raises the question of whether the universe\'s physical constants are fine-tuned for the emergence of life', 'translation' => 'Антропный принцип поднимает вопрос о том, настроены ли физические константы вселенной для возникновения жизни', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'CRISPR gene-editing technology has opened unprecedented possibilities while simultaneously raising profound ethical dilemmas', 'translation' => 'Технология редактирования генов CRISPR открыла беспрецедентные возможности, одновременно поставив глубокие этические дилеммы', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The second law of thermodynamics, with its concept of entropy, has far-reaching implications beyond physics', 'translation' => 'Второй закон термодинамики с его концепцией энтропии имеет далеко идущие последствия за пределами физики', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The theory of punctuated equilibrium challenges the gradualist model of evolutionary change', 'translation' => 'Теория прерывистого равновесия бросает вызов градуалистской модели эволюционных изменений', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'Neuroscience is increasingly encroaching on questions that were once the exclusive province of philosophy', 'translation' => 'Нейронаука всё больше вторгается в вопросы, которые когда-то были исключительной областью философии', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The Fermi paradox highlights the profound tension between the apparent likelihood of extraterrestrial life and the absence of evidence for it', 'translation' => 'Парадокс Ферми подчёркивает глубокое противоречие между очевидной вероятностью внеземной жизни и отсутствием доказательств её существования', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'Dark matter and dark energy constitute the vast majority of the universe\'s mass-energy content, yet remain poorly understood', 'translation' => 'Тёмная материя и тёмная энергия составляют подавляющее большинство массово-энергетического содержимого вселенной, однако остаются слабо изученными', 'difficulty_level' => 7, 'topic' => 'Science'],
            ['text' => 'The hard problem of consciousness — explaining why there is subjective experience at all — remains intractable', 'translation' => 'Трудная проблема сознания — объяснение того, почему вообще существует субъективный опыт, — остаётся неразрешимой', 'difficulty_level' => 7, 'topic' => 'Science'],

            // Literary sophistication
            ['text' => 'Her prose, imbued with a melancholic yet defiant spirit, encapsulates the zeitgeist of a generation grappling with existential uncertainties', 'translation' => 'Ее проза, проникнутая меланхоличным, но вызывающим духом, воплощает дух времени поколения, борющегося с экзистенциальными неопределенностями', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The narrative employs an intricate web of metaphors that simultaneously obfuscate and illuminate the underlying themes', 'translation' => 'Повествование использует сложную сеть метафор, которые одновременно затемняют и освещают основные темы', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The unreliable narrator destabilises the reader\'s assumptions and implicates them in the construction of meaning', 'translation' => 'Ненадёжный рассказчик разрушает допущения читателя и вовлекает его в конструирование смысла', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The novel\'s polyphonic structure resists any single authoritative interpretation', 'translation' => 'Полифоническая структура романа сопротивляется любой единственной авторитетной интерпретации', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'Intertextuality permeates the work, creating a palimpsest of literary allusions that rewards the erudite reader', 'translation' => 'Интертекстуальность пронизывает произведение, создавая палимпсест литературных аллюзий, вознаграждающий эрудированного читателя', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The author\'s deployment of free indirect discourse blurs the boundary between narrator and character', 'translation' => 'Использование автором несобственно-прямой речи размывает границу между рассказчиком и персонажем', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The elegiac tone of the final chapter retroactively reframes the entire narrative as an act of mourning', 'translation' => 'Элегический тон финальной главы ретроспективно переосмысляет всё повествование как акт скорби', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The text\'s self-referential quality draws attention to the artifice of narrative construction itself', 'translation' => 'Саморефлексивное качество текста привлекает внимание к искусственности самого нарративного конструирования', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The grotesque imagery serves as a vehicle for social critique, exposing the absurdity of prevailing norms', 'translation' => 'Гротескные образы служат средством социальной критики, обнажая абсурдность господствующих норм', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The author\'s use of stream of consciousness technique captures the fragmented and associative nature of thought', 'translation' => 'Использование автором техники потока сознания передаёт фрагментированный и ассоциативный характер мышления', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The spatial metaphors that structure the text encode a complex ideological geography', 'translation' => 'Пространственные метафоры, структурирующие текст, кодируют сложную идеологическую географию', 'difficulty_level' => 7, 'topic' => 'Literature'],

            // Sophisticated critique
            ['text' => 'The methodological approach, while ostensibly rigorous, fails to account for confounding variables that could potentially invalidate the conclusions', 'translation' => 'Методологический подход, хотя и кажется строгим, не учитывает мешающие переменные, которые потенциально могут аннулировать выводы', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'This reductionist perspective, though intellectually expedient, fundamentally misconstrues the multifaceted nature of the phenomenon in question', 'translation' => 'Эта редукционистская перспектива, хотя и интеллектуально целесообразна, фундаментально искажает многогранную природу рассматриваемого явления', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The author\'s ideological presuppositions are never made explicit, yet they pervasively shape the analysis', 'translation' => 'Идеологические предпосылки автора никогда не делаются явными, однако они повсеместно формируют анализ', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The selective use of evidence constitutes a form of confirmation bias that undermines the study\'s validity', 'translation' => 'Избирательное использование доказательств представляет собой форму предвзятости подтверждения, подрывающей достоверность исследования', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The conceptual apparatus deployed in this analysis is ill-suited to the complexity of the phenomena under examination', 'translation' => 'Концептуальный аппарат, используемый в этом анализе, плохо приспособлен к сложности изучаемых явлений', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The argument\'s internal coherence is compromised by a series of unacknowledged contradictions', 'translation' => 'Внутренняя согласованность аргумента нарушается рядом непризнанных противоречий', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The policy recommendation, however well-intentioned, is predicated on assumptions that are empirically contestable', 'translation' => 'Политическая рекомендация, какими бы благими намерениями она ни руководствовалась, основана на эмпирически оспоримых допущениях', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The work\'s rhetorical sophistication masks a conceptual imprecision that ultimately undermines its persuasive force', 'translation' => 'Риторическая изощрённость работы скрывает концептуальную неточность, которая в конечном счёте подрывает её убедительную силу', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The author conflates two distinct phenomena, thereby generating a spurious explanatory framework', 'translation' => 'Автор смешивает два различных явления, тем самым порождая ложную объяснительную схему', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'The normative claims embedded in the analysis are presented as if they were empirical findings', 'translation' => 'Нормативные утверждения, встроенные в анализ, представлены так, как будто они являются эмпирическими выводами', 'difficulty_level' => 7, 'topic' => 'Critique'],

            // Refined expression
            ['text' => 'The confluence of these factors precipitated an unprecedented transformation that reverberated throughout the entire sociopolitical landscape', 'translation' => 'Слияние этих факторов спровоцировало беспрецедентную трансформацию, которая отразилась на всем социально-политическом ландшафте', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'His perspicacious observations, delivered with characteristic eloquence, illuminated aspects of the subject matter that had hitherto remained obscure', 'translation' => 'Его проницательные наблюдения, высказанные с характерным красноречием, осветили аспекты предмета, которые до сих пор оставались неясными', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The ineffable quality of certain aesthetic experiences resists systematic articulation', 'translation' => 'Невыразимое качество некоторых эстетических переживаний сопротивляется систематической артикуляции', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'Her circumlocutory manner of speaking belied a remarkably incisive intelligence', 'translation' => 'Её окольная манера говорить скрывала замечательно острый ум', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The laconic precision of his prose stands in stark contrast to the baroque extravagance of his contemporaries', 'translation' => 'Лаконичная точность его прозы резко контрастирует с барочной экстравагантностью его современников', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The aphoristic density of the text rewards repeated readings, each yielding new layers of meaning', 'translation' => 'Афористическая плотность текста вознаграждает повторные прочтения, каждое из которых открывает новые пласты смысла', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The pellucid clarity of her argument cuts through the obfuscation that has long surrounded this debate', 'translation' => 'Кристальная ясность её аргумента прорезает туман, который долго окружал эту дискуссию', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'His mordant wit serves as both a rhetorical weapon and a defence mechanism against the absurdity of the world', 'translation' => 'Его едкое остроумие служит одновременно риторическим оружием и защитным механизмом против абсурдности мира', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The cadences of her speech carry an almost musical quality that lends additional persuasive force to her words', 'translation' => 'Ритм её речи несёт почти музыкальное качество, придающее дополнительную убедительную силу её словам', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'The studied ambiguity of the title invites multiple interpretive possibilities from the outset', 'translation' => 'Намеренная двусмысленность названия с самого начала открывает множество интерпретационных возможностей', 'difficulty_level' => 7, 'topic' => 'Expression'],

            // Complex reasoning
            ['text' => 'The argument, while superficially compelling, rests upon premises that are themselves contestable and require substantiation', 'translation' => 'Аргумент, хотя и поверхностно убедительный, опирается на предпосылки, которые сами по себе оспоримы и требуют обоснования', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'To extrapolate from these findings without acknowledging their inherent limitations would be intellectually dishonest', 'translation' => 'Экстраполировать эти результаты без признания их присущих ограничений было бы интеллектуально нечестно', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The recursive nature of this problem means that any proposed solution generates new problems of equivalent complexity', 'translation' => 'Рекурсивный характер этой проблемы означает, что любое предложенное решение порождает новые проблемы эквивалентной сложности', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The ad hominem fallacy, however rhetorically satisfying, diverts attention from the substantive issues at stake', 'translation' => 'Аргумент ad hominem, каким бы риторически удовлетворительным он ни был, отвлекает внимание от существенных вопросов', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The incommensurability of competing value systems makes any straightforward resolution of this dilemma impossible', 'translation' => 'Несоизмеримость конкурирующих систем ценностей делает любое прямолинейное разрешение этой дилеммы невозможным', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'Abductive reasoning, or inference to the best explanation, is the mode of reasoning most commonly employed in scientific discovery', 'translation' => 'Абдуктивное рассуждение, или умозаключение к наилучшему объяснению, является наиболее распространённым способом рассуждения в научных открытиях', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The paradox of tolerance suggests that unlimited tolerance must ultimately lead to the disappearance of tolerance itself', 'translation' => 'Парадокс терпимости предполагает, что неограниченная терпимость в конечном счёте должна привести к исчезновению самой терпимости', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The distinction between necessary and sufficient conditions is crucial for a rigorous analysis of causation', 'translation' => 'Различие между необходимыми и достаточными условиями имеет решающее значение для строгого анализа причинности', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'Thought experiments, from Plato\'s cave to Rawls\'s veil of ignorance, have been indispensable tools of philosophical inquiry', 'translation' => 'Мысленные эксперименты, от пещеры Платона до завесы незнания Ролза, были незаменимыми инструментами философского исследования', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'The regress problem in epistemology asks how any belief can be justified without appealing to an infinite chain of prior justifications', 'translation' => 'Проблема регресса в эпистемологии спрашивает, как любое убеждение может быть обосновано без апелляции к бесконечной цепи предшествующих обоснований', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
        ];
    }
}
