<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            $table->boolean('bulk_sell_enabled')->default(false)->after('shipping_fee');
            $table->unsignedInteger('bulk_sell_min_qty')->nullable()->after('bulk_sell_enabled');
            $table->decimal('bulk_sell_discount', 5, 2)->nullable()->after('bulk_sell_min_qty');
        });
    }

    public function down()
    {
        Schema::table('artwork_sells', function (Blueprint $table) {
            $table->dropColumn(['bulk_sell_enabled', 'bulk_sell_min_qty', 'bulk_sell_discount']);
        });
    }
};
