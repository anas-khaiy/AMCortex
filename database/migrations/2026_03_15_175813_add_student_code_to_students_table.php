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
    Schema::table('students', function (Blueprint $table) {
        // On ajoute la colonne après l'ID
        // On la met en 'nullable' temporairement si tu as déjà des étudiants
        $table->string('student_code')->nullable()->unique()->after('id');
    });
}

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn('student_code');
    });
}
};
