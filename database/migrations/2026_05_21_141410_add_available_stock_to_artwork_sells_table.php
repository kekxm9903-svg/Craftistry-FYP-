<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            // Place after the status column
            $table->unsignedInteger('available_stock')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            $table->dropColumn('available_stock');
        });
    }
};