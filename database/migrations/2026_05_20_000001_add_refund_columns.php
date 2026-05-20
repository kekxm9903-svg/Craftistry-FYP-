<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('refund_status', ['none','requested','approved','refunded','rejected'])->default('none')->after('status');
            $table->text('refund_reason')->nullable()->after('refund_status');
            $table->text('refund_reject_reason')->nullable()->after('refund_reason');
            $table->string('stripe_refund_id')->nullable()->after('refund_reject_reason');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('stripe_refund_id');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_amount');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
        });

        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->enum('refund_status', ['none','requested','approved','refunded','rejected'])->default('none')->after('status');
            $table->text('refund_reason')->nullable()->after('refund_status');
            $table->text('refund_reject_reason')->nullable()->after('refund_reason');
            $table->string('stripe_refund_id')->nullable()->after('refund_reject_reason');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('stripe_refund_id');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_amount');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
        });

        Schema::table('custom_order_requests', function (Blueprint $table) {
            $table->enum('refund_status', ['none','requested','approved','refunded','rejected'])->default('none')->after('status');
            $table->text('refund_reason')->nullable()->after('refund_status');
            $table->text('refund_reject_reason')->nullable()->after('refund_reason');
            $table->string('stripe_refund_id')->nullable()->after('refund_reject_reason');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('stripe_refund_id');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_amount');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
        });
    }

    public function down(): void
    {
        $cols = ['refund_status','refund_reason','refund_reject_reason','stripe_refund_id','refund_amount','refund_requested_at','refunded_at'];
        foreach (['orders','bulk_orders','custom_order_requests'] as $tbl) {
            Schema::table($tbl, function (Blueprint $t) use ($cols) {
                $t->dropColumn($cols);
            });
        }
    }
};