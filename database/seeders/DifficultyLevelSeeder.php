<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Education\DifficultyLevel;
use Illuminate\Database\Seeder;

class DifficultyLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'All', 'value' => 0, 'description' => 'All levels'],
            ['name' => 'A1', 'value' => 1, 'description' => 'Beginner'],
            ['name' => 'A2', 'value' => 2, 'description' => 'Elementary'],
            ['name' => 'B1', 'value' => 3, 'description' => 'Intermediate'],
            ['name' => 'B2', 'value' => 4, 'description' => 'Upper Intermediate'],
            ['name' => 'C1', 'value' => 5, 'description' => 'Advanced'],
            ['name' => 'C2', 'value' => 6, 'description' => 'Proficiency'],
        ];

        foreach ($levels as $level) {
            DifficultyLevel::updateOrCreate(
                ['name' => $level['name']],
                $level,
            );
        }
    }
}
