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
        Schema::create('video_paragraphs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paragraph_id')->unique()->constrained('paragraphs')->onDelete('cascade');
            $table->string('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_paragraphs');
    }
};
