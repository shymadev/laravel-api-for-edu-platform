<?php

declare(strict_types=1);

$catalog = require __DIR__.'/course_catalog.php';

/** @var list<array<string, mixed>> $bundles */
$bundles = require __DIR__.'/course_payload_bundles.php';

$uuid = static function (): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0x0fff) | 0x4000,
        random_int(0, 0x3fff) | 0x8000,
        random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0xffff)
    );
};

$mp = static function (string $left, string $right) use ($uuid): array {
    return [
        'id' => $uuid(),
        'left' => $left,
        'right' => $right,
    ];
};

$ci = static function (string $text, string $cat) use ($uuid): array {
    return [
        'id' => $uuid(),
        'text' => $text,
        'correctCategory' => $cat,
    ];
};

/**
 * Deterministic shuffle: correct answer index varies (not always the second option).
 *
 * @param  list<array{text: string, options: list<string>, correctOptions: list<int>}>  $questions
 * @return list<array<string, mixed>>
 */
$shuffleQuizQuestions = static function (array $questions, string $payloadKey): array {
    $out = [];
    foreach ($questions as $qi => $q) {
        $opts = $q['options'];
        $n = count($opts);
        if ($n < 2) {
            $out[] = $q;
            continue;
        }

        $perm = range(0, $n - 1);
        $seed = crc32($payloadKey.'|quiz|'.$qi) & 0x7fffffff;
        for ($i = $n - 1; $i > 0; $i--) {
            $seed = (int) (($seed * 1103515245 + 12345) & 0x7fffffff);
            $j = $seed % ($i + 1);
            [$perm[$i], $perm[$j]] = [$perm[$j], $perm[$i]];
        }

        $newOptions = [];
        foreach ($perm as $oldIdx) {
            $newOptions[] = $opts[$oldIdx];
        }

        $newCorrect = [];
        foreach ($q['correctOptions'] as $oldCorrect) {
            $pos = array_search((int) $oldCorrect, $perm, true);
            if ($pos !== false) {
                $newCorrect[] = (int) $pos;
            }
        }
        sort($newCorrect);

        $out[] = [
            'text' => $q['text'],
            'options' => $newOptions,
            'correctOptions' => $newCorrect,
        ];
    }

    return $out;
};

$videos = [
    'https://www.youtube.com/watch?v=IgpI5hyjXrY',
    'https://www.youtube.com/watch?v=SZD7wyAkVWk',
    'https://www.youtube.com/watch?v=6_5_WLJwToQ',
    'https://www.youtube.com/watch?v=W1nY8XLz8dA',
    'https://www.youtube.com/watch?v=KcQU8HfD0r4',
    'https://www.youtube.com/watch?v=O1isXhZkY6k',
    'https://www.youtube.com/watch?v=hv8u-X_BK3w',
    'https://www.youtube.com/watch?v=6eZPyjUV0wM',
    'https://www.youtube.com/watch?v=7zS1tQv8QWg',
    'https://www.youtube.com/watch?v=Wo0K6Nrh8EY',
    'https://www.youtube.com/watch?v=2ePf9br1SjU',
    'https://www.youtube.com/watch?v=Kl1vC1hP7UI',
    'https://www.youtube.com/watch?v=8r-EKCssrxw',
    'https://www.youtube.com/watch?v=0BDxIFVzO7E',
    'https://www.youtube.com/watch?v=O1dOPJxJ9gY',
];

/**
 * @param  array<string, mixed>  $b
 * @param  list<string>  $videos
 * @return array<string, mixed>
 */
$build = static function (
    string $payloadKey,
    string $cefr,
    string $courseTitle,
    string $themeTitle,
    string $lessonTitle,
    int $part,
    array $b,
    array $videos,
    callable $mp,
    callable $ci,
) use ($shuffleQuizQuestions): array {
    $pfx = 'k'.preg_replace('/[^a-zA-Z0-9]+/', '_', $payloadKey);
    $catA = "{$pfx}_A";
    $catB = "{$pfx}_B";

    $vid = $videos[crc32($payloadKey) % count($videos)];

    $partLabel = $part === 1 ? 'language focus' : 'practice in context';
    $introHeader = "{$themeTitle} — {$partLabel}";
    $introParagraph = "This {$cefr} lesson is part of «{$courseTitle}». Theme: «{$themeTitle}». "
        .($part === 1
            ? 'You study core chunks, pronunciation hooks, and patterns you can recycle.'
            : 'You use the theme in games and short tasks: listening, matching, and controlled production.');

    $listeningText = "Unit: {$themeTitle}. Lesson: {$lessonTitle}. Level {$cefr}. "
        .'Listen for useful chunks and notice how they sit in simple sentences. '
        .'Pause and repeat the final sentence to build rhythm and confidence.';

    $summary = "You completed «{$lessonTitle}» under «{$themeTitle}». "
        .($part === 1
            ? 'Preview the paired practice lesson next to activate this vocabulary.'
            : 'Try writing two original sentences that reuse today’s best chunk.');

    $objectives = [
        "Work with language tied to «{$themeTitle}»",
        $part === 1 ? 'Recognise, pronounce, and sort key chunks' : 'Apply chunks in guided exercises',
        "Stay within typical {$cefr} sentence length and clarity",
    ];

    $pairs = [];
    foreach ($b['pairs_lr'] as $pr) {
        $pairs[] = $mp($pr[0], $pr[1]);
    }

    $catItems = [];
    foreach ($b['cat_rows'] as $row) {
        $catItems[] = $ci($row[0], $row[1] === 'A' ? $catA : $catB);
    }

    return [
        'intro_header' => $introHeader,
        'intro_paragraph' => $introParagraph,
        'objectives' => $objectives,
        'video_url' => $vid,
        'phrases' => $b['phrases'],
        'listening_text' => $listeningText,
        'scramble_words' => $b['scramble_words'],
        'theory_header' => $b['theory_header'],
        'theory_paragraph' => $b['theory_paragraph'].' Link: «'.$themeTitle.'» in «'.$courseTitle.'».',
        'quote' => $b['quote'],
        'quote_author' => $b['quote_author'],
        'speech_phrase' => $b['speech_phrase'],
        'matching_pairs' => $pairs,
        'listen_write_items' => [
            ['type' => 'tts', 'text' => $b['lw'][0], 'correctText' => $b['lw'][0]],
            ['type' => 'tts', 'text' => $b['lw'][1], 'correctText' => $b['lw'][1]],
        ],
        'fill_gaps_text' => $b['fill_gaps_text'],
        'fill_gaps' => $b['fill_gaps'],
        'guess_items' => $b['guess_items'],
        'categories' => [
            ['id' => $catA, 'name' => $b['cat_a_name'], 'color' => $b['cat_a_color']],
            ['id' => $catB, 'name' => $b['cat_b_name'], 'color' => $b['cat_b_color']],
        ],
        'categorization_items' => $catItems,
        'quiz_questions' => $shuffleQuizQuestions($b['quiz_questions'], $payloadKey),
        'summary' => $summary,
    ];
};

$out = [];
$bundleCount = count($bundles);

foreach ($catalog['courses'] as $course) {
    foreach ($course['themes'] as $theme) {
        foreach ($theme['lessons'] as $lesson) {
            $key = $lesson['payload'];
            $part = str_ends_with($key, '_1') ? 1 : 2;
            $bi = crc32($key) % $bundleCount;
            $b = $bundles[$bi];
            if ($part === 2) {
                $b = $bundles[($bi + 5) % $bundleCount];
            }

            $out[$key] = $build(
                $key,
                $course['level'],
                $course['title'],
                $theme['title'],
                $lesson['title'],
                $part,
                $b,
                $videos,
                $mp,
                $ci
            );
        }
    }
}

return $out;
