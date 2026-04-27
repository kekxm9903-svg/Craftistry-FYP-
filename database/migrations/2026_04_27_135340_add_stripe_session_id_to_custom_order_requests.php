<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_order_requests', function (Blueprint $table) {
            $table->string('stripe_session_id')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('custom_order_requests', function (Blueprint $table) {
            $table->dropColumn('stripe_session_id');
        });
    }
};