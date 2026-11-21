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
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->json('changes')->nullable()->comment('JSON diff of changes');
            
            // Revision metadata
            $table->string('revision_type')->default('auto'); // auto, manual, restore
            $table->integer('revision_number')->default(1);
            $table->string('status')->default('inherit');
            $table->timestamp('revision_date')->useCurrent();
            $table->timestamp('revision_date_gmt')->useCurrent();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};
