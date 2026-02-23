<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('difficulty_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('value')->unique();
            $table->string('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('difficulty_levels');
    }
};
