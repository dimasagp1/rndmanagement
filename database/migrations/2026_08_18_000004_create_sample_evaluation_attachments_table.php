<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_evaluation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sample_evaluation_sessions')->onDelete('cascade');
            $table->enum('type', ['Form Panel', 'Blind Code', 'Report Panel Test', 'Data Panelis', 'Result']);
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_evaluation_attachments');
    }
};