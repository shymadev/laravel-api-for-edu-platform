<?php

namespace Database\Seeders;

use App\Models\Education\Course;
use App\Models\Education\Topic;
use App\Models\Education\Lesson;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    // ... imports and boilerplate

    public function run(): void
    {
         $courses = [
            'A1' => ['title' => 'Beginner', 'topics' => [/* 5 topics */]],
            // ... 7 courses
         ];
         
         // Loop courses -> create
         // Loop topics -> create
         // Loop subtopics -> create
         // Loop lessons -> generateRichContent()
    }

    private function generateRichContent($topic, $level): array 
    {
        // 1. Detailed Theory (EditorJS)
        // 2. Vocabulary/Phrases
        // 3. Audio/Video (placeholders or re-used)
        // 4. Interactive Practice (Matching, Gaps, Tests)
        return [];
    }
}
