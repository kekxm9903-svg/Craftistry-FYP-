<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artwork_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert the artwork types
        DB::table('artwork_types')->insert([
            [
                'name' => 'Drawing',
                'description' => 'Pencil, charcoal, ink, and digital drawings',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Knitting',
                'description' => 'Hand-knitted items including scarves, sweaters, and accessories',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Crochet',
                'description' => 'Crocheted items like blankets, toys, and clothing',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Perler Beads',
                'description' => 'Colorful bead art and pixel designs',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Embroidery',
                'description' => 'Hand-stitched and machine embroidery designs',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Beadwork',
                'description' => 'Beaded jewelry, accessories, and decorative items',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_types');
    }
};