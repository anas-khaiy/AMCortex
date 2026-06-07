<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up()
{

 Schema::create('questions', function (Blueprint $table) {

    $table->id();

    $table->foreignId('exam_id')->constrained()->onDelete('cascade');

    $table->text('question_text');

    $table->enum('question_type',['single','multiple', 'boolean']);

    $table->float('points_correct')->default(1);  // Ex: 1 point
 
    $table->float('points_penalty')->default(0);  // Ex: -0.5 point

    $table->boolean('shuffle_answers')->default(false);

    $table->text('explanation')->nullable();

    $table->timestamps();

 });

}

public function down()
{
Schema::dropIfExists('questions');
}

};