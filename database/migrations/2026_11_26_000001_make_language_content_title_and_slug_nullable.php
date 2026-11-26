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
     *
     * Works on both SQLite (local/tests) and MySQL (production).
     */
    public function up(): void
    {
        if (!Schema::hasTable('language_content')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        // Read the current nullability of title/slug.
        if ($driver === 'sqlite') {
            $columns = DB::select('PRAGMA table_info(language_content)');
            $title = collect($columns)->firstWhere('name', 'title');
            $slug = collect($columns)->firstWhere('name', 'slug');
            $alreadyNullable = $title && $slug && (int) $title->notnull === 0 && (int) $slug->notnull === 0;
        } else {
            $columns = DB::select('SHOW COLUMNS FROM language_content');
            $title = collect($columns)->firstWhere('Field', 'title');
            $slug = collect($columns)->firstWhere('Field', 'slug');
            $alreadyNullable = $title && $slug && strtoupper($title->Null) === 'YES' && strtoupper($slug->Null) === 'YES';
        }

        // Already correct (e.g. fresh installs running the fixed original migration).
        if ($alreadyNullable) {
            return;
        }

        DB::statement('ALTER TABLE language_content RENAME TO language_content_old');

        // The recreated table reuses the same index names, so the old table's
        // indexes must be dropped first. SQLite keeps index names on rename;
        // MySQL does as well.
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS language_content_content_type_content_id_language_id_unique');
            DB::statement('DROP INDEX IF EXISTS language_content_content_type_content_id_index');
            DB::statement('DROP INDEX IF EXISTS language_content_language_id_index');
        } else {
            foreach ([
                'language_content_content_type_content_id_language_id_unique',
                'language_content_content_type_content_id_index',
                'language_content_language_id_index',
            ] as $index) {
                DB::statement("ALTER TABLE language_content_old DROP INDEX {$index}");
            }
        }

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

        // Bump the auto-increment counter so the next row gets a fresh id.
        if ($driver !== 'sqlite') {
            DB::statement('ALTER TABLE language_content AUTO_INCREMENT = (SELECT COALESCE(MAX(id), 0) + 1 FROM language_content)');
        }

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
