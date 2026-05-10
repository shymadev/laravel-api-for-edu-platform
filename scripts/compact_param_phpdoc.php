<?php

declare(strict_types=1);

/**
 * Collapses @param lines to "@param {type} {name}" (drops trailing description).
 * Run from api/: php scripts/compact_param_phpdoc.php
 */
$roots = [
    __DIR__ . '/../app',
    __DIR__ . '/../config',
    __DIR__ . '/../database',
    __DIR__ . '/../routes',
    __DIR__ . '/../tests',
];

// Match a @param line that has extra text after the variable (description).
// 1) Drop text after the variable: @param int $id   Lesson
$stripDescription = '/^
(?P<indent>\h*\*\h*)
@param\h+
(?P<type>.+?)
\h+
(?P<var>(?:\.\.\.)?&?\$\S+)
(?P<rest>
\h
.+
)
$
/mx';

// 2) Remove padding between type and name (no description): @param int      $id
$collapseGap = '/^
(?P<indent>\h*\*\h*)
@param\h+
(?P<type>.+?)
\h{2,}
(?P<var>(?:\.\.\.)?&?\$\S+)
\h*$
/mx';

$filesTouched = 0;

foreach ($roots as $root) {
    if (! is_dir($root)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    /** @var SplFileInfo $file */
    foreach ($it as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $code = (string) file_get_contents($path);
        if (! str_contains($code, '@param')) {
            continue;
        }
        $new = preg_replace_callback(
            $stripDescription,
            static function (array $m): string {
                $type = trim((string) preg_replace('/\s+/', ' ', $m['type']), " \t");

                return $m['indent'].'@param '.$type.' '.$m['var'];
            },
            $code
        );
        $code = $new ?? $code;
        $new = preg_replace_callback(
            $collapseGap,
            static function (array $m): string {
                $type = trim((string) preg_replace('/\s+/', ' ', $m['type']), " \t");

                return $m['indent'].'@param '.$type.' '.$m['var'];
            },
            $code
        );
        if ($new !== $code) {
            file_put_contents($path, $new);
            ++$filesTouched;
        }
    }
}

fwrite(STDOUT, "Files updated: {$filesTouched}\n");
