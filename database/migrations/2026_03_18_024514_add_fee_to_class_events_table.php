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
        Schema::table('class_events', function (Blueprint $table) {
            // Add after max_participants if it exists, otherwise just append
            $table->boolean('is_paid')->default(false)->after('max_participants');
            $table->decimal('price', 10, 2)->nullable()->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_events', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'price']);
        });
    }
};