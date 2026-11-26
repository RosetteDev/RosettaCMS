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
        Schema::create('language_content', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 50)->comment='Type of content (post, page, category, tag)';
            $table->unsignedBigInteger('content_id')->comment='ID of the original content';
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable()->comment='Translated title';
            $table->text('content')->nullable()->comment='Translated content';
            $table->string('slug')->nullable()->comment='Translated slug';
            $table->text('excerpt')->nullable()->comment='Translated excerpt';
            $table->json('meta')->nullable()->comment='Additional translated metadata';
            $table->unsignedBigInteger('translated_by')->nullable()->comment='User who performed translation';
            $table->timestamps();

            // Composite unique key to prevent duplicate translations
            $table->unique(['content_type', 'content_id', 'language_id']);

            // Index for faster lookups
            $table->index(['content_type', 'content_id']);
            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_content');
    }
};
