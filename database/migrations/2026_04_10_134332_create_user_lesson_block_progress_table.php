<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_lesson_block_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->unsignedSmallInteger('block_index');
            $table->string('block_type', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'lesson_id', 'block_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_block_progress');
    }
};
