<?php

declare(strict_types=1);

/**
 * 17 courses × 5 themes × 2 lessons = 170 lessons (7 free, 10 premium).
 * Payload keys: c{courseId}_t{theme1-5}_l{1|2}
 */
$mkCourse = static function (
    int $id,
    string $level,
    string $title,
    string $description,
    bool $isPremium,
    array $themeTitles,
): array {
    $themes = [];
    foreach ($themeTitles as $i => $themeTitle) {
        $t = $i + 1;
        $themes[] = [
            'title' => $themeTitle,
            'lessons' => [
                [
                    'title' => "{$themeTitle}: language focus",
                    'payload' => "c{$id}_t{$t}_1",
                ],
                [
                    'title' => "{$themeTitle}: practice in context",
                    'payload' => "c{$id}_t{$t}_2",
                ],
            ],
        ];
    }

    return [
        'id' => $id,
        'level' => $level,
        'title' => $title,
        'description' => $description,
        'is_premium' => $isPremium,
        'themes' => $themes,
    ];
};

return [
    'courses' => [
        $mkCourse(
            1,
            'A1',
            'Everyday English: Life in the City',
            'A1: приветствия, дорога, еда, покупки и время. Десять уроков на бытовые ситуации.',
            false,
            [
                'Greetings & politeness',
                'Directions & transport',
                'Food & cafés',
                'Shopping & prices',
                'Time & daily routine',
            ],
        ),
        $mkCourse(
            2,
            'A1',
            'People, Home & Hobbies',
            'A1: семья, описание людей, дом, свободное время и простые сообщения.',
            false,
            [
                'Family & people',
                'Describing appearance',
                'Rooms & furniture',
                'Free time & hobbies',
                'Notes & short messages',
            ],
        ),
        $mkCourse(
            3,
            'A2',
            'Travel Smart: Trips & Transport',
            'A2: аэропорты, отели, еда в поездке, помощь и достопримечательности.',
            true,
            [
                'Airports & tickets',
                'Hotels & bookings',
                'Eating out abroad',
                'Problems & emergencies',
                'Sightseeing & tours',
            ],
        ),
        $mkCourse(
            4,
            'A2',
            'Study Skills & First Jobs',
            'A2: язык учёбы, дедлайны, подработка, простое резюме и онлайн-занятия.',
            true,
            [
                'Classroom language',
                'Homework & deadlines',
                'Part-time work',
                'CV & application basics',
                'Online learning',
            ],
        ),
        $mkCourse(
            5,
            'B1',
            'Office English in Action',
            'B1: письма, встречи, звонки, small talk и короткие отчёты.',
            true,
            [
                'Professional email',
                'Meetings & agendas',
                'Phone & video calls',
                'Small talk at work',
                'Status notes & reports',
            ],
        ),
        $mkCourse(
            6,
            'B1',
            'Life Admin & Services',
            'B1: врач, жильё, банк, ремонт и жалобы — язык повседневных дел.',
            true,
            [
                'Health & pharmacy',
                'Renting & housing',
                'Banking & payments',
                'Repairs & appointments',
                'Complaints & solutions',
            ],
        ),
        $mkCourse(
            7,
            'B2',
            'Debating the News',
            'B2: политика, экономика, этика, интервью и передовицы.',
            true,
            [
                'Politics & institutions',
                'Economy & markets',
                'Ethics & dilemmas',
                'Interview & Q&A',
                'Opinion & editorials',
            ],
        ),
        $mkCourse(
            8,
            'B2',
            'Science & Data for Readers',
            'B2: медицинские тексты, космос, климат, технологии и статистика.',
            true,
            [
                'Health & research',
                'Space & exploration',
                'Climate & evidence',
                'Tech & innovation',
                'Charts & statistics',
            ],
        ),
        $mkCourse(
            9,
            'B2',
            'Culture, Film & the Arts',
            'B2: кино, музеи, музыка, литература и идентичность.',
            true,
            [
                'Film & series',
                'Museums & heritage',
                'Music & performance',
                'Books & stories',
                'Identity & society',
            ],
        ),
        $mkCourse(
            10,
            'C1',
            'Precision, Tone & Voice',
            'C1: образность, регистр, ирония, юридический тон и академическое hedging.',
            true,
            [
                'Figurative language',
                'Register & stance',
                'Irony & implication',
                'Legal & formal tone',
                'Academic hedging',
            ],
        ),
        $mkCourse(
            11,
            'C1',
            'Discourse & Argumentation',
            'C1: связность, аргументация, контраргументы, конспект и перефраз.',
            true,
            [
                'Cohesion & reference',
                'Argument structure',
                'Counter-arguments',
                'Summaries & synthesis',
                'Paraphrase & citation',
            ],
        ),
        $mkCourse(
            12,
            'B2',
            'Fluency & Interaction',
            'B2: рассказы, мнения под давлением, уточнение, питч и юмор в общении.',
            true,
            [
                'Storytelling & narrative',
                'Opinions under pressure',
                'Clarifying & repairing talk',
                'Short pitches & introductions',
                'Humour & rapport',
            ],
        ),
        $mkCourse(
            13,
            'A1',
            'English at School: Classroom & Friends',
            'A1: школа, расписание, одноклассники и простые правила.',
            false,
            [
                'Classroom objects & colours',
                'Days & school subjects',
                'Friends & age',
                'Classroom rules (simple)',
                'After-school clubs',
            ],
        ),
        $mkCourse(
            14,
            'A2',
            'Health & Body Basics',
            'A2: самочувствие, врач, аптека и простые советы.',
            false,
            [
                'Symptoms & how you feel',
                'Doctor visit phrases',
                'Pharmacy & medicine labels',
                'Food & healthy habits',
                'Sleep & stress (simple)',
            ],
        ),
        $mkCourse(
            15,
            'B1',
            'Social English: Friends & Neighbours',
            'B1: приглашения, соседи, мелкие просьбы и вежливый отказ.',
            false,
            [
                'Invitations & plans',
                'Neighbours & noise',
                'Favours & saying no politely',
                'Social media & messaging',
                'Small celebrations',
            ],
        ),
        $mkCourse(
            16,
            'B2',
            'Sports & Fitness Talk',
            'B2: виды спорта, тренировки, соревнования и здоровье.',
            false,
            [
                'Training & routines',
                'Matches & commentary',
                'Injuries & recovery',
                'Nutrition for athletes',
                'Fans & ethics in sport',
            ],
        ),
        $mkCourse(
            17,
            'C1',
            'English for Exams: Essays & Speaking',
            'C1: структура эссе, связующие слова, устные ответы под давлением.',
            false,
            [
                'Essay structure & thesis',
                'Cohesive devices in writing',
                'Speaking: long turns',
                'Counter-argument drills',
                'Paraphrasing under time pressure',
            ],
        ),
    ],
];
