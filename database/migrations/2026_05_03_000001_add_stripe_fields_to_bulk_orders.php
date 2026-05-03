<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('bulk_orders', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bulk_orders', 'order_id')) {
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->after('stripe_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['stripe_session_id', 'order_id']);
        });
    }
};