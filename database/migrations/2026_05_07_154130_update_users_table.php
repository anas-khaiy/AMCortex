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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'first_name')) {
            $table->string('first_name')->nullable()->after('username');
        }

        if (!Schema::hasColumn('users', 'last_name')) {
            $table->string('last_name')->nullable()->after('first_name');
        }
    });

    DB::statement("UPDATE users SET username = CONCAT('user_', id) WHERE username IS NULL OR username = ''");
    DB::statement("UPDATE users SET first_name = username WHERE first_name IS NULL OR first_name = ''");
    DB::statement("UPDATE users SET last_name = '' WHERE last_name IS NULL");

    try {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    } catch (\Throwable $e) {
        // Index already exists
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
