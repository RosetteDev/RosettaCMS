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
        Schema::table('site_settings', function (Blueprint $table) {
            // Logo options
            $table->string('logo_url')->nullable()->after('theme');
            $table->string('logo_text')->nullable()->after('logo_url');
            
            // Favicon/App Icon
            $table->string('favicon_url')->nullable()->after('logo_text');
            
            // Color options
            $table->string('primary_color')->default('#3b82f6')->after('favicon_url');
            $table->string('secondary_color')->default('#6b7280')->after('primary_color');
            $table->string('accent_color')->default('#10b981')->after('secondary_color');
            $table->string('text_color')->default('#1f2937')->after('accent_color');
            $table->string('bg_color')->default('#ffffff')->after('text_color');
            $table->string('border_color')->default('#e5e7eb')->after('bg_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'logo_text', 'favicon_url', 'primary_color', 'secondary_color', 'accent_color', 'text_color', 'bg_color', 'border_color']);
        });
    }
};
