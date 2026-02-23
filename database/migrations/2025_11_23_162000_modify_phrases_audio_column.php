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
        Schema::table('phrases', function (Blueprint $table) {
            // Change audio column from MEDIUMBLOB to string (VARCHAR)
            $table->string('audio', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phrases', function (Blueprint $table) {
            // Revert back to MEDIUMBLOB
            $table->binary('audio')->nullable()->change();
        });

        DB::statement('ALTER TABLE `phrases` MODIFY `audio` MEDIUMBLOB NULL;');
    }
};
