<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->string('enrollment_form_url')->nullable()->after('enrollment_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->dropColumn('enrollment_form_url');
        });
    }
};