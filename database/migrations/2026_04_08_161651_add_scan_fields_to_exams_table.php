<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('scan_status')->default('pending')->after('shuffle_answers');
            $table->text('last_amc_log')->nullable()->after('scan_status');
            $table->text('last_amc_error')->nullable()->after('last_amc_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'scan_status',
                'last_amc_log',
                'last_amc_error',
            ]);
        });
    }
};
