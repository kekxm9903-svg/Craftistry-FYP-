<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_order_requests', function (Blueprint $table) {
            $table->id();

            // Parties
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();

            // Buyer inputs
            $table->string('title', 120);
            $table->text('description');
            $table->string('reference_image')->nullable();     // storage path
            $table->decimal('buyer_price', 10, 2);            // buyer's offered price

            // Status lifecycle
            // pending   → waiting for seller to respond
            // accepted  → seller accepted, buyer must pay
            // refused   → seller refused (may include counter_price)
            // completed → buyer paid, real order created
            // cancelled → buyer refused counter-price
            $table->enum('status', [
                'pending',
                'accepted',
                'refused',
                'completed',
                'cancelled',
            ])->default('pending');

            // Seller response fields
            $table->text('seller_reason')->nullable();
            $table->decimal('counter_price', 10, 2)->nullable();

            // Buyer reaction to a counter-price
            $table->enum('buyer_response', ['pending', 'accepted', 'refused'])->nullable();

            // Linked real order after payment completes
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_requests');
    }
};