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
        Schema::table('user_lesson_block_progress', function (Blueprint $table): void {
            $table->boolean('is_completed')->default(false)->after('block_type');
            $table->json('block_state')->nullable()->after('is_completed');
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_lesson_block_progress', function (Blueprint $table): void {
            $table->dropColumn(['is_completed', 'block_state', 'updated_at']);
        });
    }
};
