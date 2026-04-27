<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            // null = no limit
            $table->unsignedInteger('max_participants')->nullable()->after('enrollment_form_url');
        });
    }

    public function down(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->dropColumn('max_participants');
        });
    }
};