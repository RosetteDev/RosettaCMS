<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('enable_social_login_google')->default(false)->after('enable_user_registration');
            $table->boolean('enable_social_login_github')->default(false)->after('enable_social_login_google');
            $table->boolean('enable_welcome_email')->default(true)->after('enable_social_login_github');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_social_login_google',
                'enable_social_login_github',
                'enable_welcome_email',
            ]);
        });
    }
};
