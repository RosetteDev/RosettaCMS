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
            // User preferences for admin interface
            $table->json('preferences')->nullable()->after('password');
            
            // Admin-specific settings
            $table->string('admin_theme')->default('light')->after('preferences');
            $table->boolean('show_welcome_tour')->default(true)->after('admin_theme');
            $table->json('dashboard_widgets')->nullable()->after('show_welcome_tour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferences', 'admin_theme', 'show_welcome_tour', 'dashboard_widgets']);
        });
    }
};
