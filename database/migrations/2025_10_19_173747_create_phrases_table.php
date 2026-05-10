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
        Schema::create('phrases', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->string('text');
            $table->text('translation')->nullable();
            $table->unsignedTinyInteger('difficulty_level')->default(0);
            $table->string('topic')->nullable();
            $table->binary('audio')->nullable();
        });

        DB::statement('ALTER TABLE `phrases` MODIFY `audio` MEDIUMBLOB NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phrases');
    }
};
