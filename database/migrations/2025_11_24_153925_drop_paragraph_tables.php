<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('test_options');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('test_paragraphs');
        Schema::dropIfExists('phrase_items');
        Schema::dropIfExists('phrase_paragraphs');
        Schema::dropIfExists('text_paragraphs');
        Schema::dropIfExists('video_paragraphs');
        Schema::dropIfExists('paragraphs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
