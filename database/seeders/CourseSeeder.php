<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\DifficultyLevel;
use Database\Seeders\Importers\CourseImporter;
use Database\Seeders\Importers\JsonCourseParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds courses, topics and lessons from JSON definition files.
 *
 * Scans database/seeders/data/courses/ recursively for *.json files,
 * uploads SVG preview images to MinIO, then delegates each file to
 * {@see CourseImporter}. Already-existing courses (matched by title) are skipped.
 */
class CourseSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $this->command->info('Starting course seeding...');

        $imageUrlMap = $this->publishImagesToMinio();

        $parser = new JsonCourseParser();
        $levelMap = DifficultyLevel::pluck('id', 'name');
        $importer = new CourseImporter($parser, $levelMap, $imageUrlMap);

        $files = $this->collectJsonFiles(database_path('seeders/data/courses'));
        $total = count($files);

        if ($total === 0) {
            $this->command->warn('No JSON course files found.');

            return;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $progressBar = $this->command->getOutput()->createProgressBar($total);
        $progressBar->start();

        foreach ($files as $file) {
            try {
                $raw = json_decode(File::get($file), true);
                $title = $raw['title'] ?? '';

                if ($title !== '' && Course::query()->where('title', $title)->exists()) {
                    $skipped++;
                    $progressBar->advance();

                    continue;
                }

                $importer->import($file);
                $created++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('CourseSeeder: failed to import', [
                    'file' => $file,
                    'error' => $e->getMessage(),
                ]);
                $this->command->newLine();
                $this->command->warn('Failed [' . $file . ']: ' . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        $this->command->info("Courses seeded: {$created} created, {$skipped} skipped, {$failed} failed.");
    }

    /**
     * Return sorted absolute paths of all JSON files under the given directory.
     *
     * @param string $directory
     *
     * @return string[]
     */
    private function collectJsonFiles(string $directory): array
    {
        if (!File::isDirectory($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->filter(fn ($f) => $f->getExtension() === 'json')
            ->map(fn ($f) => $f->getPathname())
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Upload SVG placeholder images to MinIO and return a filename → URL map.
     *
     * @return Collection<string, string>
     */
    private function publishImagesToMinio(): Collection
    {
        $src = database_path('seeders/data/images/courses');
        $urlMap = collect();

        if (!File::isDirectory($src)) {
            $this->command->warn('No course images directory found — preview_image will be null.');

            return $urlMap;
        }

        foreach (File::files($src) as $file) {
            $objectKey = 'images/courses/' . $file->getFilename();

            try {
                if (!Storage::disk('minio')->exists($objectKey)) {
                    Storage::disk('minio')->put(
                        $objectKey,
                        File::get($file->getPathname()),
                        'public',
                    );
                }

                $urlMap->put($file->getFilename(), Storage::disk('minio')->url($objectKey));
            } catch (\Throwable $e) {
                $this->command->warn('Could not upload image ' . $file->getFilename() . ' to MinIO: ' . $e->getMessage());
            }
        }

        if ($urlMap->isNotEmpty()) {
            $this->command->info("Uploaded {$urlMap->count()} course images to MinIO.");
        }

        return $urlMap;
    }
}
