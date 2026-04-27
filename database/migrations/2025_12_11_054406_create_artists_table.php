<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('specialization')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('allow_customization')->default(false);
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            // Add index for better query performance
            $table->index('verification_status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};