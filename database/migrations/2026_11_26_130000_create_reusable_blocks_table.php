<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Reusable blocks are named templates of one or more root blocks. The
     * payload is stored as a JSON array of root blocks (type/content/settings);
     * the editor operates on top-level blocks only, so container blocks with
     * nested children are excluded at save time.
     */
    public function up(): void
    {
        Schema::create('reusable_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reusable_blocks');
    }
};
