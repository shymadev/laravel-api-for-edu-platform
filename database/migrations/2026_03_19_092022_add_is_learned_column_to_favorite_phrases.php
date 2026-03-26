<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('favorite_phrases', function (Blueprint $table) {
            $table->boolean('is_learned')->default(false)->after('phrase_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favourite_phrases', function (Blueprint $table) {
            $table->dropColumn('is_learned');
        });
    }
};
