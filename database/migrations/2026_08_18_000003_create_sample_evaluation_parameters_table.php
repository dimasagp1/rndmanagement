<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_evaluation_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sample_evaluation_sessions')->onDelete('cascade');
            $table->enum('parameter', ['Rasa', 'Warna', 'Aroma', 'Tekstur', 'After Taste']);
            $table->enum('score', ['Baik', 'Cukup', 'Kurang']);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_evaluation_parameters');
    }
};