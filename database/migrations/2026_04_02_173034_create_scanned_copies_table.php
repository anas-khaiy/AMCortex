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
    Schema::create('scanned_copies', function (Blueprint $table) {
        $table->id();
        $table->foreignId('exam_id')->constrained()->onDelete('cascade');
        $table->string('file_path');
        $table->string('original_filename');
        $table->string('mime_type')->nullable();
        $table->bigInteger('file_size')->nullable();
        $table->string('status')->default('pending'); // pending, processing, completed, error
        $table->text('error_message')->nullable();
        $table->timestamp('upload_date')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scanned_copies');
    }
};
