<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('locked_at')->useCurrent();
            $table->timestamp('heartbeat_at')->useCurrent();
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
            $table->index(['post_id', 'heartbeat_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_locks');
    }
};
