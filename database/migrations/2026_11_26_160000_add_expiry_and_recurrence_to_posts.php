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
        Schema::table('posts', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('published_at');
            $table->string('repeat_frequency', 20)->nullable()->after('expires_at');
            $table->date('repeat_until')->nullable()->after('repeat_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'repeat_frequency', 'repeat_until']);
        });
    }
};
