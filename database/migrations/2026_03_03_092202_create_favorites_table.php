<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('artist_id')
                  ->constrained('users')   // also references the users table
                  ->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate favorites
            $table->unique(['user_id', 'artist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};