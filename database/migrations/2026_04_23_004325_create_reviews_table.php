<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');      // buyer
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');    // seller
            $table->unsignedBigInteger('artwork_sell_id')->nullable();
            $table->foreign('artwork_sell_id')->references('id')->on('artwork_sells')->onDelete('set null');
            $table->tinyInteger('rating')->unsigned();                // 1–5 stars
            $table->text('description')->nullable();                  // review text
            $table->string('image_path')->nullable();                 // uploaded photo
            $table->string('video_path')->nullable();                 // uploaded video
            $table->boolean('is_anonymous')->default(false);          // hide buyer name
            $table->timestamps();

            $table->unique(['order_id', 'user_id']);                  // one review per order
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};