<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_evaluation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_evaluation_id')->constrained('sample_evaluations')->onDelete('cascade');
            $table->unsignedInteger('session_no'); // Nomor sesi evaluasi
            $table->unsignedInteger('trial_batch'); // Trial batch (number)
            $table->enum('evaluator_type', ['Internal', 'External']); // Internal/External evaluator
            $table->text('evaluation_result')->nullable();
            $table->text('sensory_result')->nullable();
            $table->enum('decision', ['Approved', 'Reform'])->nullable(); // Pass/Fail
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_evaluation_sessions');
    }
};