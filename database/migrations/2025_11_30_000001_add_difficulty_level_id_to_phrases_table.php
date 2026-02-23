<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('phrases', function (Blueprint $table) {
            if (Schema::hasColumn('phrases', 'difficulty_level')) {
                $table->dropColumn('difficulty_level');
            }
            $table->foreignId('difficulty_level_id')->nullable()->constrained('difficulty_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phrases', function (Blueprint $table) {
            $table->dropForeign(['difficulty_level_id']);
            $table->dropColumn('difficulty_level_id');
            $table->integer('difficulty_level')->nullable(); // Restore old column type (approx)
        });
    }
};
