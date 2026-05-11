<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_ic_path')->nullable()->after('postcode');
            $table->string('kyc_selfie_path')->nullable()->after('kyc_ic_path');
            $table->enum('kyc_status', ['pending', 'passed', 'failed'])->default('pending')->after('kyc_selfie_path');
            $table->float('kyc_similarity')->nullable()->after('kyc_status');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_similarity');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_ic_path',
                'kyc_selfie_path',
                'kyc_status',
                'kyc_similarity',
                'kyc_verified_at',
            ]);
        });
    }
};