<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original language_content migration declared title and slug as
     * NOT NULL, which does not match how translations are used (partial
     * translations are legitimate). Recreate the table with nullable
     * columns, preserving any rows.
     */
    public function up(): void
    {
        if (!Schema::hasTable('language_content')) {
            return;
        }

        $columns = DB::select('PRAGMA table_info(language_content)');
        $title = collect($columns)->firstWhere('name', 'title');
        $slug = collect($columns)->firstWhere('name', 'slug');

        // Already correct (e.g. fresh installs running the fixed original migration).
        if ($title && $slug && (int) $title->notnull === 0 && (int) $slug->notnull === 0) {
            return;
        }

        DB::statement('ALTER TABLE language_content RENAME TO language_content_old');

        // SQLite keeps index names on rename; drop them so the recreated
        // table can reuse the same names. The old table is about to be dropped.
        DB::statement('DROP INDEX IF EXISTS language_content_content_type_content_id_language_id_unique');
        DB::statement('DROP INDEX IF EXISTS language_content_content_type_content_id_index');
        DB::statement('DROP INDEX IF EXISTS language_content_language_id_index');

        Schema::create('language_content', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 50)->comment('Type of content (post, page, category, tag)');
            $table->unsignedBigInteger('content_id')->comment('ID of the original content');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable()->comment('Translated title');
            $table->text('content')->nullable()->comment('Translated content');
            $table->string('slug')->nullable()->comment('Translated slug');
            $table->text('excerpt')->nullable()->comment('Translated excerpt');
            $table->json('meta')->nullable()->comment('Additional translated metadata');
            $table->unsignedBigInteger('translated_by')->nullable()->comment('User who performed translation');
            $table->timestamps();

            $table->unique(['content_type', 'content_id', 'language_id']);
            $table->index(['content_type', 'content_id']);
            $table->index('language_id');
        });

        DB::statement('INSERT INTO language_content (id, content_type, content_id, language_id, title, content, slug, excerpt, meta, translated_by, created_at, updated_at) SELECT id, content_type, content_id, language_id, title, content, slug, excerpt, meta, translated_by, created_at, updated_at FROM language_content_old');

        Schema::drop('language_content_old');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as a no-op: re-adding NOT NULL constraints would
        // fail on rows with null title/slug.
    }
};
