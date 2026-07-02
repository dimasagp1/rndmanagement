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
        Schema::create('trial_rms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // TRM-YYYYMM-XXX-A
            $table->foreignId('formula_id')->constrained('formulas')->onDelete('cascade');
            $table->string('sample_identity'); // Identitas Sampel
            $table->text('process_steps'); // Tahapan Proses
            $table->enum('decision', ['Lulus', 'Reformulasi'])->nullable();
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'])->default('Draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_rms');
    }
};
