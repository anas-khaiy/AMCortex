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
        if (!Schema::hasColumn('students', 'student_code')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('student_code')->nullable()->unique()->after('id');
            });
        }
    }

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn('student_code');
    });
}
};
