<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bulk_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_sell_id')->constrained('artwork_sells')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('quantity');
            $table->date('last_ship_date');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discounted_price', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'refused'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bulk_orders');
    }
};
