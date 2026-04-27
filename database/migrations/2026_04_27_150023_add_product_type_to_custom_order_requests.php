<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_order_requests', function (Blueprint $table) {
            $table->enum('product_type', ['physical', 'digital'])
                  ->default('physical')
                  ->after('buyer_price');
        });
    }

    public function down(): void
    {
        Schema::table('custom_order_requests', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
};