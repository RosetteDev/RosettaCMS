<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_strings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255);
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('value', 500);
            $table->timestamps();

            $table->unique(['key', 'language_id']);
            $table->index(['key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_strings');
    }
};
