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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path', 512);
            $table->string('title', 255)->nullable();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('referrer', 512)->nullable();
            $table->string('device', 32)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('visitor_hash', 64)->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
            $table->index(['post_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
