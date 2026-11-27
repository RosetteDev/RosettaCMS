<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // SEO defaults
            $table->string('seo_title_template')->nullable()->after('seo_title_suffix');
            $table->string('og_image')->nullable()->after('seo_default_keywords');
            $table->string('twitter_handle')->nullable()->after('og_image');
            $table->text('verification_meta')->nullable()->after('twitter_handle');
            $table->text('robots_disallow')->nullable()->after('verification_meta');
            $table->text('robots_allow')->nullable()->after('robots_disallow');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title_template',
                'og_image',
                'twitter_handle',
                'verification_meta',
                'robots_disallow',
                'robots_allow',
            ]);
        });
    }
};
