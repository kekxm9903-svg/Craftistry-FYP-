<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('verification_status');
        });
    }

    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
        });
    }
};
