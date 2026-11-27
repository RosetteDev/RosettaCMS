<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugin_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id', 255)->index();
            $table->string('permission', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('approved_at');

            $table->unique(['plugin_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_approvals');
    }
};
