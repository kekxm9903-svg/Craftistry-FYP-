<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Skip user_id, booking_id, type, total, stripe_session_id, 
            // payment_status, payment_method — already added earlier
            // Just add the missing old columns
            $table->foreignId('artist_id')->nullable()->constrained('artists')->onDelete('set null')->after('payment_method');
            $table->string('title')->nullable()->after('artist_id');
            $table->text('description')->nullable()->after('title');
            $table->decimal('price', 10, 2)->nullable()->after('description');
            $table->string('status')->default('pending')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['artist_id', 'title', 'description', 'price', 'status']);
        });
    }
};