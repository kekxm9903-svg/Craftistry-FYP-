<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('poster_image')->nullable();
            
            // Media Type (online/physical)
            $table->enum('media_type', ['online', 'physical'])->default('online');
            $table->string('platform')->nullable();
            $table->string('location')->nullable();
            
            // Date Range
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('duration_weeks', 5, 1)->nullable();
            
            // Time Range
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours')->nullable();
            $table->integer('duration_minutes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_events');
    }
};