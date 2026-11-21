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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO 639-1 language code (e.g., en, fr, es)');
            $table->string('name', 100)->comment('Display name (e.g., English, Français)');
            $table->string('native_name', 100)->nullable()->comment('Native name (e.g., English, Français)');
            $table->string('flag', 10)->nullable()->comment('Flag emoji or code (e.g., 🇺🇸, 🇫🇷)');
            $table->boolean('is_default')->default(false)->comment='Default language for the site';
            $table->boolean('is_active')->default(true)->comment='Whether this language is enabled';
            $table->integer('sort_order')->default(0)->comment='Display order';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
