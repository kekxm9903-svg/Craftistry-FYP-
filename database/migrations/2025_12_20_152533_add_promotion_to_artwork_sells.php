<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            $table->boolean('promotion_enabled')->default(false)->after('bulk_sell_discount');
            $table->decimal('promotion_discount', 5, 2)->nullable()->after('promotion_enabled');
            $table->timestamp('promotion_starts_at')->nullable()->after('promotion_discount');
            $table->timestamp('promotion_ends_at')->nullable()->after('promotion_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            $table->dropColumn(['promotion_enabled', 'promotion_discount', 'promotion_starts_at', 'promotion_ends_at']);
        });
    }
};