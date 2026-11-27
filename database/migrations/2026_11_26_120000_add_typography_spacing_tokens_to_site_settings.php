<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('body_font_size', 20)->default('16px')->after('container_width');
            $table->string('line_height', 10)->default('1.6')->after('body_font_size');
            $table->string('block_gap', 20)->default('2rem')->after('line_height');
            $table->string('section_padding', 20)->default('3rem')->after('block_gap');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['body_font_size', 'line_height', 'block_gap', 'section_padding']);
        });
    }
};
