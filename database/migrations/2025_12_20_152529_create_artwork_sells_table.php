<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artwork_sells', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to artists table
            $table->foreignId('artist_id')->constrained('artists')->onDelete('cascade');
            
            // Standard Product Info
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->decimal('product_price', 10, 2);
            $table->string('image_path');
            
            // Artwork details
            $table->enum('artwork_type', ['physical', 'digital'])->default('physical'); 
            $table->string('material')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->string('unit', 10)->default('cm');

            // Status field - available or sold_out
            $table->enum('status', ['available', 'sold_out'])->default('available');
            
            // Cross-posting fields
            $table->boolean('is_cross_posted')->default(false);
            $table->unsignedBigInteger('cross_posted_from_id')->nullable();
            
            $table->timestamps();
            
            // DON'T add foreign key here - will add later
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_sells');
    }
};