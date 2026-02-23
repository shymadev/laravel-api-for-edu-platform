<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('test_questions')->onDelete('cascade');
            $table->string('text');
            $table->boolean('is_correct')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_options');
    }
};
