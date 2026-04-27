<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status')->default('free')->after('booked_at');
            // free | pending | paid | failed
            $table->string('stripe_session_id')->nullable()->after('payment_status');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('stripe_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'stripe_session_id', 'amount_paid']);
        });
    }
};