<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phrase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('phrase_paragraph_id')->constrained('phrase_paragraphs')->onDelete('cascade');
            $table->string('text');
            $table->string('translation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phrase_items');
    }
};
