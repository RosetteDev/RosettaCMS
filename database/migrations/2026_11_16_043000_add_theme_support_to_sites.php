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
        Schema::table('sites', function (Blueprint $table) {
            // Add theme columns
            $table->string('theme')->default('rosetta')->after('home_post_id');
            $table->text('theme_options')->nullable()->after('theme');
            
            // Add theme options to sites_settings table
            $table->string('logo')->nullable()->after('site_id');
            $table->string('favicon')->nullable()->after('logo');
            $table->string('primary_color')->nullable()->after('favicon');
            $table->string('secondary_color')->nullable()->after('primary_color');
            $table->string('accent_color')->nullable()->after('secondary_color');
            $table->string('background_color')->nullable()->after('accent_color');
            $table->string('text_color')->nullable()->after('background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['theme', 'theme_options']);
            $table->dropColumn(['logo', 'favicon', 'primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color']);
        });
    }
};
