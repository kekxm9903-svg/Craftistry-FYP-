<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending','accepted','refused','completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending','accepted','refused') NOT NULL DEFAULT 'pending'");
    }
};