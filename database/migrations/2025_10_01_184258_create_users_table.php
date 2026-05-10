<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->boolean('is_blocked')->default(false);
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->timestamps(true);

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
