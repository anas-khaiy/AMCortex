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
    Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('course_name');
        $table->string('teacher_name')->nullable();
        $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
        
        $table->integer('duration')->default(60);
        $table->float('total_points', 10, 2)->default(0);
        $table->date('exam_date')->nullable();
        $table->string('exam_language')->default('fr');
        
        $table->string('page_format')->default('A4'); 
        
        $table->integer('copies_number')->default(1); 
        $table->integer('student_id_length')->default(6); 
        $table->text('instructions')->nullable(); 
        
        $table->boolean('shuffle_questions')->default(false); // Ajouté
        $table->boolean('shuffle_answers')->default(false); // Ajouté
        

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
