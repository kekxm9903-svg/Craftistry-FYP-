<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->string('instagram_url', 2048)->nullable()->after('enrollment_form_url');
            $table->string('facebook_url',  2048)->nullable()->after('instagram_url');
            $table->string('x_url',         2048)->nullable()->after('facebook_url');
        });
    }

    public function down(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'facebook_url', 'x_url']);
        });
    }
};