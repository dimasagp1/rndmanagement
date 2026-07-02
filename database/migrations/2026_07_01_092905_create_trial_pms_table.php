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
        Schema::create('trial_pms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // TPM-YYYYMM-XXX
            $table->string('packaging_material'); // Nama bahan kemas
            $table->text('specifications'); // Spesifikasi fisik
            $table->json('parameters')->nullable(); // Parameter pelaksanaan (speed, suhu, tekanan)
            $table->text('risk_analysis')->nullable(); // Analisis risiko
            $table->enum('approval_status', ['Draft', 'Pending Review', 'Approved', 'Rejected'])->default('Draft');
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
        Schema::dropIfExists('trial_pms');
    }
};
