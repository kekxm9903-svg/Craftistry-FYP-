<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_event_id')->constrained('class_events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('booked_at')->useCurrent();
            $table->timestamps();

            // Prevent duplicate bookings
            $table->unique(['class_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};