<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_artworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->integer('order')->default(0);
            
            // Cross-posting fields
            $table->boolean('is_cross_posted')->default(false);
            $table->unsignedBigInteger('cross_posted_to_id')->nullable();
            
            // Additional fields for cross-posting to artwork_sells
            $table->enum('artwork_type', ['physical', 'digital'])->default('physical');
            $table->string('material')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->string('unit', 10)->default('cm');
            $table->decimal('price', 10, 2)->nullable();
            
            $table->timestamps();
            
            // DON'T add foreign key here - will add later
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_artworks');
    }
};