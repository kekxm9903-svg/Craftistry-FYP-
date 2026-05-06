<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            // preferred_artwork_type already exists from a previous migration — skip it
            // Only add preference_shown
            if (!Schema::hasColumn("users", "preference_shown")) {
                $table->boolean("preference_shown")->default(false)->after("preferred_artwork_type");
            }
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (Schema::hasColumn("users", "preference_shown")) {
                $table->dropColumn("preference_shown");
            }
        });
    }
};