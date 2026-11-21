<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scope content slugs to their site instead of making them globally unique.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->unique(['site_id', 'slug']);
        });
    }

    /**
     * Restore globally unique taxonomy slugs.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->unique(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['slug']);
        });
    }
};
