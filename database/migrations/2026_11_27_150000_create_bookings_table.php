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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service', 255)->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->index();
            $table->string('phone', 255)->nullable();
            $table->date('date')->index();
            $table->string('time', 16)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 16)->default('pending')->index(); // pending, confirmed, completed, cancelled
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
