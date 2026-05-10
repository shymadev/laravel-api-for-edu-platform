<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DifficultyLevelSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PhraseSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PremiumSubscriptionSeeder::class);
        $this->call(UserProgressSeeder::class);
        $this->call(LessonBlockProgressSeeder::class);
        $this->call(CourseReviewSeeder::class);
        $this->call(FavouritePhrasesSeeder::class);
    }
}
