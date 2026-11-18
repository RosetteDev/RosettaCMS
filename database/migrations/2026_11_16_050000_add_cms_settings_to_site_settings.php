<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // General / contact
            $table->string('admin_email')->nullable()->after('site_description');

            // Comments
            $table->boolean('enable_comment_approval')->default(true)->after('disable_comments');

            // Spam protection
            $table->boolean('enable_akismet')->default(false)->after('enable_comment_approval');
            $table->string('akismet_api_key')->nullable()->after('enable_akismet');
            $table->text('spam_keywords')->nullable()->after('akismet_api_key');
            $table->boolean('rate_limit_enabled')->default(true)->after('spam_keywords');
            $table->unsignedInteger('rate_limit_per_minute')->default(3)->after('rate_limit_enabled');
            $table->boolean('captcha_enabled')->default(false)->after('rate_limit_per_minute');

            // Display / theme
            $table->string('font_family')->default('system-ui, sans-serif')->after('border_color');
            $table->string('container_width')->default('1200px')->after('font_family');

            // Permalinks
            $table->string('permalink_structure')->default('post_name')->after('container_width');
            $table->string('custom_permalink')->nullable()->after('permalink_structure');

            // SEO
            $table->string('seo_title_suffix')->nullable()->after('custom_permalink');
            $table->text('seo_default_keywords')->nullable()->after('seo_title_suffix');
            $table->boolean('enable_xml_sitemap')->default(true)->after('seo_default_keywords');

            // Security / uploads
            $table->boolean('enable_debug_mode')->default(false)->after('enable_xml_sitemap');
            $table->boolean('disable_file_editor')->default(false)->after('enable_debug_mode');
            $table->unsignedInteger('max_upload_size')->default(10)->after('disable_file_editor');
            $table->boolean('enable_user_registration')->default(false)->after('max_upload_size');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'admin_email',
                'enable_comment_approval',
                'enable_akismet',
                'akismet_api_key',
                'spam_keywords',
                'rate_limit_enabled',
                'rate_limit_per_minute',
                'captcha_enabled',
                'font_family',
                'container_width',
                'permalink_structure',
                'custom_permalink',
                'seo_title_suffix',
                'seo_default_keywords',
                'enable_xml_sitemap',
                'enable_debug_mode',
                'disable_file_editor',
                'max_upload_size',
                'enable_user_registration',
            ]);
        });
    }
};
