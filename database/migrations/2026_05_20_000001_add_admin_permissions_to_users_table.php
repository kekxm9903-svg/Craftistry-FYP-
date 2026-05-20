<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand the role ENUM to include 'super_admin'
        // Adjust the existing values below to match your current ENUM definition exactly
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','super_admin','buyer') NOT NULL DEFAULT 'user'");

        // Step 2: Add the admin_permissions JSON column
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'admin_permissions')) {
                $table->json('admin_permissions')->nullable()->after('role');
            }
        });

        // Step 3: Promote the oldest admin to super_admin
        DB::statement("UPDATE users SET role = 'super_admin' WHERE role = 'admin' ORDER BY id LIMIT 1");
    }

    public function down(): void
    {
        // Demote super_admin back to admin
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'super_admin'");

        // Remove admin_permissions column
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'admin_permissions')) {
                $table->dropColumn('admin_permissions');
            }
        });

        // Shrink the ENUM back — remove 'super_admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','admin','buyer') NOT NULL DEFAULT 'user'");
    }
};