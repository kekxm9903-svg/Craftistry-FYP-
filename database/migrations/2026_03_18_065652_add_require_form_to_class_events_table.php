<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->boolean('require_form')->default(false)->after('max_participants');
        });
    }

    public function down(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->dropColumn('require_form');
        });
    }
};
