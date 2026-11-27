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
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret', 64)->nullable()->after('password');
            $table->dateTime('two_factor_enabled_at')->nullable()->after('two_factor_secret');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('two_factor_enabled_at');
            $table->dateTime('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled_at', 'failed_login_attempts', 'locked_until']);
        });
    }
};
