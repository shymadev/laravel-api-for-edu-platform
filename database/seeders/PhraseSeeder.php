<?php

namespace Database\Seeders;

use App\Models\Education\Phrase;
use App\Services\Storage\AudioStorageService;
use App\Services\TTSService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PhraseSeeder extends Seeder
{
    protected TTSService $ttsService;

    protected AudioStorageService $audioStorage;

    public function __construct()
    {
        $this->ttsService = new TTSService();
        $this->audioStorage = new AudioStorageService();

    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting phrase seeding...');

        $ttsAvailable = $this->ttsService->isHealthy();

        if (! $ttsAvailable) {
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

                Phrase::create([
                    'text' => $phraseData['text'],
                    'translation' => $phraseData['translation'],
                    'difficulty_level_id' => $phraseData['difficulty_level'],
                    'topic' => $phraseData['topic'],
                    'audio' => $audioPath,
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

        $this->command->info("Seeding finished:");
        $this->command->info("Created: {$created} / {$total}");
        $this->command->warn("Failed: {$failed}");
    }

    /**
     * Get all phrases organized by difficulty level and topic.
     *
     * @return array
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
            $this->getC2Phrases()
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

            // Animals
            ['text' => 'Cat', 'translation' => 'Кошка', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Dog', 'translation' => 'Собака', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Bird', 'translation' => 'Птица', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Fish', 'translation' => 'Рыба', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Horse', 'translation' => 'Лошадь', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Cow', 'translation' => 'Корова', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Pig', 'translation' => 'Свинья', 'difficulty_level' => 1, 'topic' => 'Animals'],
            ['text' => 'Mouse', 'translation' => 'Мышь', 'difficulty_level' => 1, 'topic' => 'Animals'],

            // Common objects
            ['text' => 'Book', 'translation' => 'Книга', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Table', 'translation' => 'Стол', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Chair', 'translation' => 'Стул', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Door', 'translation' => 'Дверь', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Window', 'translation' => 'Окно', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Phone', 'translation' => 'Телефон', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Pen', 'translation' => 'Ручка', 'difficulty_level' => 1, 'topic' => 'Objects'],
            ['text' => 'Paper', 'translation' => 'Бумага', 'difficulty_level' => 1, 'topic' => 'Objects'],

            // Body parts
            ['text' => 'Head', 'translation' => 'Голова', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Eye', 'translation' => 'Глаз', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Ear', 'translation' => 'Ухо', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Nose', 'translation' => 'Нос', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Mouth', 'translation' => 'Рот', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Hand', 'translation' => 'Рука', 'difficulty_level' => 1, 'topic' => 'Body'],
            ['text' => 'Foot', 'translation' => 'Нога', 'difficulty_level' => 1, 'topic' => 'Body'],

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

            // Shopping
            ['text' => 'How much is this?', 'translation' => 'Сколько это стоит?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I want to buy this', 'translation' => 'Я хочу купить это', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you have this in blue?', 'translation' => 'У вас есть это в синем?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'I need a bag', 'translation' => 'Мне нужна сумка', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Can I try this on?', 'translation' => 'Могу я это примерить?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'This is too expensive', 'translation' => 'Это слишком дорого', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Do you accept credit cards?', 'translation' => 'Вы принимаете кредитные карты?', 'difficulty_level' => 2, 'topic' => 'Shopping'],
            ['text' => 'Where is the nearest store?', 'translation' => 'Где ближайший магазин?', 'difficulty_level' => 2, 'topic' => 'Shopping'],

            // Feelings
            ['text' => 'I am happy', 'translation' => 'Я счастлив', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am sad', 'translation' => 'Мне грустно', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am tired', 'translation' => 'Я устал', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am excited', 'translation' => 'Я взволнован', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am worried', 'translation' => 'Я беспокоюсь', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am fine', 'translation' => 'У меня все хорошо', 'difficulty_level' => 2, 'topic' => 'Feelings'],
            ['text' => 'I am angry', 'translation' => 'Я зол', 'difficulty_level' => 2, 'topic' => 'Feelings'],

            // Locations
            ['text' => 'Where is the bathroom?', 'translation' => 'Где туалет?', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'I am at home', 'translation' => 'Я дома', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'The bank is here', 'translation' => 'Банк находится здесь', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Turn left', 'translation' => 'Поверните налево', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Turn right', 'translation' => 'Поверните направо', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'Go straight', 'translation' => 'Идите прямо', 'difficulty_level' => 2, 'topic' => 'Locations'],
            ['text' => 'It is near the park', 'translation' => 'Это рядом с парком', 'difficulty_level' => 2, 'topic' => 'Locations'],
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

            // Health
            ['text' => 'I do not feel well', 'translation' => 'Я плохо себя чувствую', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a headache', 'translation' => 'У меня болит голова', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'Where is the nearest pharmacy?', 'translation' => 'Где ближайшая аптека?', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need to see a doctor', 'translation' => 'Мне нужно обратиться к врачу', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I have a fever', 'translation' => 'У меня температура', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'My stomach hurts', 'translation' => 'У меня болит живот', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I am allergic to peanuts', 'translation' => 'У меня аллергия на арахис', 'difficulty_level' => 3, 'topic' => 'Health'],
            ['text' => 'I need a prescription', 'translation' => 'Мне нужен рецепт', 'difficulty_level' => 3, 'topic' => 'Health'],

            // Weather
            ['text' => 'It is raining today', 'translation' => 'Сегодня идет дождь', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The weather is nice', 'translation' => 'Погода хорошая', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It will be sunny tomorrow', 'translation' => 'Завтра будет солнечно', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'I love winter because I can ski', 'translation' => 'Я люблю зиму, потому что могу кататься на лыжах', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is very hot today', 'translation' => 'Сегодня очень жарко', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is snowing outside', 'translation' => 'На улице идет снег', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'The temperature is below zero', 'translation' => 'Температура ниже нуля', 'difficulty_level' => 3, 'topic' => 'Weather'],
            ['text' => 'It is quite windy', 'translation' => 'Довольно ветрено', 'difficulty_level' => 3, 'topic' => 'Weather'],

            // Work
            ['text' => 'I start work at nine o clock', 'translation' => 'Я начинаю работу в девять часов', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I have a meeting this afternoon', 'translation' => 'У меня встреча сегодня днем', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'My boss is very kind', 'translation' => 'Мой начальник очень добрый', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I work from home twice a week', 'translation' => 'Я работаю из дома два раза в неделю', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I need to finish this project by Friday', 'translation' => 'Мне нужно закончить этот проект к пятнице', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'My colleagues are very helpful', 'translation' => 'Мои коллеги очень помогают', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I am looking for a new job', 'translation' => 'Я ищу новую работу', 'difficulty_level' => 3, 'topic' => 'Work'],
            ['text' => 'I have been working here for five years', 'translation' => 'Я работаю здесь пять лет', 'difficulty_level' => 3, 'topic' => 'Work'],

            // Education
            ['text' => 'I am studying computer science', 'translation' => 'Я изучаю информатику', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I have an exam next week', 'translation' => 'У меня экзамен на следующей неделе', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'This subject is difficult', 'translation' => 'Этот предмет сложный', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I need to study more', 'translation' => 'Мне нужно больше учиться', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'My teacher is excellent', 'translation' => 'Мой учитель превосходный', 'difficulty_level' => 3, 'topic' => 'Education'],
            ['text' => 'I graduated from university last year', 'translation' => 'Я закончил университет в прошлом году', 'difficulty_level' => 3, 'topic' => 'Education'],

            // Hobbies
            ['text' => 'I enjoy playing football', 'translation' => 'Мне нравится играть в футбол', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I like to paint in my free time', 'translation' => 'Я люблю рисовать в свободное время', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I collect stamps', 'translation' => 'Я коллекционирую марки', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'My passion is photography', 'translation' => 'Моя страсть - фотография', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
            ['text' => 'I go swimming every weekend', 'translation' => 'Я хожу плавать каждые выходные', 'difficulty_level' => 3, 'topic' => 'Hobbies'],
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

            // Experiences
            ['text' => 'I have never been to Japan, but I would love to go', 'translation' => 'Я никогда не был в Японии, но хотел бы поехать', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'Last summer, I traveled to France and visited the Eiffel Tower', 'translation' => 'Прошлым летом я поехал во Францию и посетил Эйфелеву башню', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I have been working on this project for three months', 'translation' => 'Я работаю над этим проектом три месяца', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'When I was a child, I used to play outside every day', 'translation' => 'Когда я был ребенком, я каждый день играл на улице', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'That was the most memorable experience of my life', 'translation' => 'Это был самый запоминающийся опыт в моей жизни', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'I will never forget the day I graduated from college', 'translation' => 'Я никогда не забуду день, когда я закончил колледж', 'difficulty_level' => 4, 'topic' => 'Experiences'],
            ['text' => 'The first time I spoke in public, I was extremely nervous', 'translation' => 'Когда я впервые выступал публично, я был очень нервным', 'difficulty_level' => 4, 'topic' => 'Experiences'],

            // Plans and goals
            ['text' => 'I am planning to start my own business next year', 'translation' => 'Я планирую начать свой бизнес в следующем году', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'My goal is to become fluent in three languages', 'translation' => 'Моя цель - свободно владеть тремя языками', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I intend to finish this course before the end of the year', 'translation' => 'Я намерен закончить этот курс до конца года', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We are going to move to a new apartment in the spring', 'translation' => 'Мы собираемся переехать в новую квартиру весной', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'By this time next year, I will have completed my degree', 'translation' => 'К этому времени в следующем году я завершу свою степень', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'I am hoping to travel more in the future', 'translation' => 'Я надеюсь больше путешествовать в будущем', 'difficulty_level' => 4, 'topic' => 'Plans'],
            ['text' => 'We are aiming to reduce costs by twenty percent', 'translation' => 'Мы стремимся снизить затраты на двадцать процентов', 'difficulty_level' => 4, 'topic' => 'Plans'],

            // Problems and solutions
            ['text' => 'The main problem is that we do not have enough resources', 'translation' => 'Основная проблема в том, что у нас недостаточно ресурсов', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'We need to find a solution to this issue as soon as possible', 'translation' => 'Нам нужно найти решение этой проблемы как можно скорее', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'If we work together, we can overcome any challenge', 'translation' => 'Если мы будем работать вместе, мы сможем преодолеть любые трудности', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'One way to solve this would be to hire more staff', 'translation' => 'Один из способов решить это - нанять больше сотрудников', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'This situation requires immediate attention', 'translation' => 'Эта ситуация требует немедленного внимания', 'difficulty_level' => 4, 'topic' => 'Problems'],
            ['text' => 'There must be a better way to handle this', 'translation' => 'Должен быть лучший способ справиться с этим', 'difficulty_level' => 4, 'topic' => 'Problems'],

            // Social situations
            ['text' => 'Would you mind if I opened the window?', 'translation' => 'Вы не возражаете, если я открою окно?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Could you please help me with this task?', 'translation' => 'Не могли бы вы помочь мне с этим заданием?', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I would appreciate it if you could send me the details', 'translation' => 'Я был бы признателен, если бы вы могли прислать мне детали', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Let me know if you need anything else', 'translation' => 'Дайте мне знать, если вам что-то еще нужно', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I was wondering if you could give me some advice', 'translation' => 'Мне было интересно, не могли бы вы дать мне совет', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'Thank you for taking the time to meet with me', 'translation' => 'Спасибо, что нашли время встретиться со мной', 'difficulty_level' => 4, 'topic' => 'Social'],
            ['text' => 'I completely understand your point of view', 'translation' => 'Я полностью понимаю вашу точку зрения', 'difficulty_level' => 4, 'topic' => 'Social'],

            // Technology
            ['text' => 'I need to update my computer software', 'translation' => 'Мне нужно обновить программное обеспечение компьютера', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Social media has changed the way we communicate', 'translation' => 'Социальные сети изменили способ общения', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I cannot live without my smartphone', 'translation' => 'Я не могу жить без смартфона', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'Online shopping is very convenient', 'translation' => 'Интернет-магазины очень удобны', 'difficulty_level' => 4, 'topic' => 'Technology'],
            ['text' => 'I prefer video calls to phone calls', 'translation' => 'Я предпочитаю видеозвонки телефонным звонкам', 'difficulty_level' => 4, 'topic' => 'Technology'],

            // Relationships
            ['text' => 'I have known my best friend since childhood', 'translation' => 'Я знаю своего лучшего друга с детства', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'Communication is key in any relationship', 'translation' => 'Общение - ключ к любым отношениям', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'We should spend more quality time together', 'translation' => 'Нам следует проводить больше качественного времени вместе', 'difficulty_level' => 4, 'topic' => 'Relationships'],
            ['text' => 'It is important to respect each other s opinions', 'translation' => 'Важно уважать мнения друг друга', 'difficulty_level' => 4, 'topic' => 'Relationships'],
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

            // Professional contexts
            ['text' => 'Our team has successfully implemented a new strategy that increased productivity by thirty percent', 'translation' => 'Наша команда успешно внедрила новую стратегию, которая увеличила производительность на тридцать процентов', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The company is committed to fostering innovation and encouraging creative thinking', 'translation' => 'Компания стремится развивать инновации и поощрять творческое мышление', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We need to reassess our approach and consider alternative solutions', 'translation' => 'Нам нужно пересмотреть наш подход и рассмотреть альтернативные решения', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'Effective leadership requires both vision and the ability to execute', 'translation' => 'Эффективное лидерство требует как видения, так и способности к исполнению', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The quarterly results exceeded our expectations significantly', 'translation' => 'Квартальные результаты значительно превысили наши ожидания', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'We must leverage our strengths while addressing our weaknesses', 'translation' => 'Мы должны использовать наши сильные стороны, устраняя слабые', 'difficulty_level' => 5, 'topic' => 'Professional'],
            ['text' => 'The merger will create synergies that benefit both organizations', 'translation' => 'Слияние создаст синергию, которая принесет пользу обеим организациям', 'difficulty_level' => 5, 'topic' => 'Professional'],

            // Abstract concepts
            ['text' => 'The relationship between culture and identity is complex and multifaceted', 'translation' => 'Связь между культурой и идентичностью сложна и многогранна', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Understanding different perspectives can broaden your worldview significantly', 'translation' => 'Понимание различных точек зрения может значительно расширить ваше мировоззрение', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The concept of sustainability has become increasingly important in modern society', 'translation' => 'Концепция устойчивого развития становится все более важной в современном обществе', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Human nature is characterized by both altruism and self-interest', 'translation' => 'Человеческая природа характеризуется как альтруизмом, так и корыстью', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'The notion of progress is relative and culturally dependent', 'translation' => 'Понятие прогресса относительно и культурно обусловлено', 'difficulty_level' => 5, 'topic' => 'Abstract'],
            ['text' => 'Democracy thrives on active citizen participation and transparency', 'translation' => 'Демократия процветает благодаря активному участию граждан и прозрачности', 'difficulty_level' => 5, 'topic' => 'Abstract'],

            // Debates and arguments
            ['text' => 'On the one hand, globalization has brought economic benefits, but on the other hand, it has led to cultural homogenization', 'translation' => 'С одной стороны, глобализация принесла экономические выгоды, но с другой стороны, она привела к культурной гомогенизации', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'There is considerable debate about whether artificial intelligence will replace human workers', 'translation' => 'Ведутся значительные дебаты о том, заменит ли искусственный интеллект работников-людей', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'While I understand your point of view, I would argue that the evidence suggests otherwise', 'translation' => 'Хотя я понимаю вашу точку зрения, я бы утверждал, что доказательства говорят об обратном', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'The benefits of free trade must be weighed against its potential drawbacks', 'translation' => 'Преимущества свободной торговли должны быть взвешены против ее потенциальных недостатков', 'difficulty_level' => 5, 'topic' => 'Debates'],
            ['text' => 'It could be argued that privacy concerns have not kept pace with technological advancement', 'translation' => 'Можно утверждать, что проблемы конфиденциальности не успевают за технологическим прогрессом', 'difficulty_level' => 5, 'topic' => 'Debates'],

            // Hypothetical situations
            ['text' => 'If I had known about the traffic, I would have left earlier', 'translation' => 'Если бы я знал о пробках, я бы уехал раньше', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Had we been more prepared, the outcome might have been different', 'translation' => 'Будь мы более подготовлены, результат мог бы быть другим', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'I wish I could speak more languages fluently', 'translation' => 'Жаль, что я не могу свободно говорить на большем количестве языков', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'Suppose you won the lottery, what would you do with the money?', 'translation' => 'Предположим, вы выиграли в лотерею, что бы вы сделали с деньгами?', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],
            ['text' => 'If I were in your position, I would consider all available options', 'translation' => 'Если бы я был на вашем месте, я бы рассмотрел все доступные варианты', 'difficulty_level' => 5, 'topic' => 'Hypothetical'],

            // Culture and society
            ['text' => 'Cultural diversity enriches our societies in countless ways', 'translation' => 'Культурное разнообразие обогащает наши общества бесчисленными способами', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The arts play a vital role in reflecting and shaping social values', 'translation' => 'Искусство играет жизненно важную роль в отражении и формировании социальных ценностей', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'Traditions should be preserved while embracing modernity', 'translation' => 'Традиции должны сохраняться при принятии современности', 'difficulty_level' => 5, 'topic' => 'Culture'],
            ['text' => 'The gap between generations can be bridged through dialogue and understanding', 'translation' => 'Разрыв между поколениями можно преодолеть через диалог и понимание', 'difficulty_level' => 5, 'topic' => 'Culture'],

            // Economics
            ['text' => 'Economic inequality has reached unprecedented levels in many countries', 'translation' => 'Экономическое неравенство достигло беспрецедентного уровня во многих странах', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Market forces alone cannot address all societal needs', 'translation' => 'Одни только рыночные силы не могут решить все потребности общества', 'difficulty_level' => 5, 'topic' => 'Economics'],
            ['text' => 'Investment in education yields long-term economic benefits', 'translation' => 'Инвестиции в образование приносят долгосрочные экономические выгоды', 'difficulty_level' => 5, 'topic' => 'Economics'],
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

            // Academic language
            ['text' => 'The study elucidates the intricate mechanisms underlying cognitive development in early childhood', 'translation' => 'Исследование прояенсяет сложные механизмы, лежащие в основе когнитивного развития в раннем детстве', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'This theory postulates that human behavior is fundamentally shaped by environmental and genetic factors', 'translation' => 'Эта теория постулирует, что поведение человека фундаментально формируется факторами окружающей среды и генетики', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The research methodology employed was designed to minimize bias and ensure reproducibility', 'translation' => 'Использованная методология исследования была разработана для минимизации предвзятости и обеспечения воспроизводимости', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'The findings challenge conventional wisdom and warrant further investigation', 'translation' => 'Результаты бросают вызов обще принятой мудрости и оправдывают дальнейшее исследование', 'difficulty_level' => 6, 'topic' => 'Academic'],
            ['text' => 'Empirical evidence substantiates the hypothesis that has been proposed', 'translation' => 'Эмпирические данные подтверждают предложенную гипотезу', 'difficulty_level' => 6, 'topic' => 'Academic'],

            // Nuanced arguments
            ['text' => 'While it would be overly simplistic to attribute the crisis to a single cause, the evidence overwhelmingly points to systemic failures', 'translation' => 'Хотя было бы чрезмерно упрощенным приписывать кризис одной причине, доказательства подавляюще указывают на системные сбои', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The dichotomy between individual freedom and collective responsibility lies at the heart of many contemporary political debates', 'translation' => 'Дихотомия между индивидуальной свободой и коллективной ответственностью лежит в основе многих современных политических дебатов', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'It is imperative that we distinguish between correlation and causation when interpreting statistical data', 'translation' => 'Крайне важно различать корреляцию и причинно-следственную связь при интерпретации статистических данных', 'difficulty_level' => 6, 'topic' => 'Arguments'],
            ['text' => 'The discourse surrounding this issue has become increasingly polarized and lacks nuance', 'translation' => 'Дискурс вокруг этого вопроса становится все более поляризованным и лишен нюансов', 'difficulty_level' => 6, 'topic' => 'Arguments'],

            // Professional expertise
            ['text' => 'Our comprehensive analysis revealed several previously unidentified vulnerabilities in the system architecture', 'translation' => 'Наш всесторонний анализ выявил несколько ранее неопознан ных уязвимостей в архитектуре системы', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The implementation of this framework necessitates a paradigm shift in how we approach organizational structure', 'translation' => 'Внедрение этой структуры требует смены парадигмы в подходе к организационной структуре', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'Strategic alignment between different departments is crucial for achieving long-term organizational objectives', 'translation' => 'Стратегическое согласование между различными отделами имеет решающее значение для достижения долгосрочных организационных целей', 'difficulty_level' => 6, 'topic' => 'Professional'],
            ['text' => 'The stakeholders have expressed concerns regarding the sustainability of the current business model', 'translation' => 'Заинтересованные стороны выразили обеспокоенность относительно устойчивости текущей бизнес-модели', 'difficulty_level' => 6, 'topic' => 'Professional'],
        ];
    }

    /**
     * C2 Level - Proficient.
     */
    protected function getC2Phrases(): array
    {
        return [
            // Mastery level
            ['text' => 'The juxtaposition of these seemingly disparate elements serves to underscore the fundamental paradox inherent in the human condition', 'translation' => 'Сопоставление этих, казалось бы, разрозненных элементов служит для подчеркивания фундаментального парадокса, присущего человеческому состоянию', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'Notwithstanding the pervasive skepticism, the empirical evidence lends credence to the hypothesis that consciousness may transcend purely materialistic explanations', 'translation' => 'Несмотря на всеобщий скептицизм, эмпирические доказательства придают правдоподобность гипотезе о том, что сознание может выходить за рамки чисто материалистических объяснений', 'difficulty_level' => 7, 'topic' => 'Philosophy'],
            ['text' => 'The epistemological implications of quantum mechanics continue to perplex even the most erudite scholars in the field', 'translation' => 'Эпистемологические последствия квантовой механики продолжают озадачивать даже самых эрудированных ученых в этой области', 'difficulty_level' => 7, 'topic' => 'Science'],

            // Literary sophistication
            ['text' => 'Her prose, imbued with a melancholic yet defiant spirit, encapsulates the zeitgeist of a generation grappling with existential uncertainties', 'translation' => 'Ее проза, проникнутая меланхоличным, но вызывающим духом, воплощает дух времени поколения, борющегося с экзистенциальными неопределенностями', 'difficulty_level' => 7, 'topic' => 'Literature'],
            ['text' => 'The narrative employs an intricate web of metaphors that simultaneously obfuscate and illuminate the underlying themes', 'translation' => 'Повествование использует сложную сеть метафор, которые одновременно затемняют и освещают основные темы', 'difficulty_level' => 7, 'topic' => 'Literature'],

            // Sophisticated critique
            ['text' => 'The methodological approach, while ostensibly rigorous, fails to account for confounding variables that could potentially invalidate the conclusions', 'translation' => 'Методологический подход, хотя и кажется строгим, не учитывает мешающие переменные, которые потенциально могут аннулировать выводы', 'difficulty_level' => 7, 'topic' => 'Critique'],
            ['text' => 'This reductionist perspective, though intellectually expedient, fundamentally misconstrues the multifaceted nature of the phenomenon in question', 'translation' => 'Эта редукционистская перспектива, хотя и интеллектуально целесообразна, фундаментально искажает многогранную природу рассматриваемого явления', 'difficulty_level' => 7, 'topic' => 'Critique'],

            // Refined expression
            ['text' => 'The confluence of these factors precipitated an unprecedented transformation that reverberated throughout the entire sociopolitical landscape', 'translation' => 'Слияние этих факторов спровоцировало беспрецедентную трансформацию, которая отразилась на всем социально-политическом ландшафте', 'difficulty_level' => 7, 'topic' => 'Expression'],
            ['text' => 'His perspicacious observations, delivered with characteristic eloquence, illuminated aspects of the subject matter that had hitherto remained obscure', 'translation' => 'Его проницательные наблюдения, высказанные с характерным красноречием, осветили аспекты предмета, которые до сих пор оставались неясными', 'difficulty_level' => 7, 'topic' => 'Expression'],

            // Complex reasoning
            ['text' => 'The argument, while superficially compelling, rests upon premises that are themselves contestable and require substantiation', 'translation' => 'Аргумент, хотя и поверхностно убедительный, опирается на предпосылки, которые сами по себе оспоримы и требуют обоснования', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
            ['text' => 'To extrapolate from these findings without acknowledging their inherent limitations would be intellectually dishonest', 'translation' => 'Экстраполировать эти результаты без признания их присущих ограничений было бы интеллектуально нечестно', 'difficulty_level' => 7, 'topic' => 'Reasoning'],
        ];
    }
}
