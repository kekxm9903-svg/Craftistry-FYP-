<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->after('id');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null')->after('user_id');
            $table->string('type')->default('class')->after('booking_id'); // class | artwork
            $table->decimal('total', 10, 2)->default(0)->after('type');
            $table->string('stripe_session_id')->nullable()->after('total');
            $table->string('payment_status')->default('pending')->after('stripe_session_id');
            // pending | paid | failed | cancelled
            $table->string('payment_method')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'booking_id', 'type', 'total', 'stripe_session_id', 'payment_status', 'payment_method']);
        });
    }
};