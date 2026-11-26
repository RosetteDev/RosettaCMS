<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 500);
            $table->string('secret', 255);
            $table->json('events')->default('[]');
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_fired_at')->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->string('last_error', 500)->nullable();
            $table->timestamps();

            $table->index(['site_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
