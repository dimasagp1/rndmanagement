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
        Schema::create('trial_pm_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_pm_id')->constrained('trial_pms')->onDelete('cascade');
            $table->enum('department', ['rd', 'qc', 'production', 'engineering']); // 4 departemen
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable(); // Catatan per departemen
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Satu departemen hanya bisa approve sekali per trial
            $table->unique(['trial_pm_id', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_pm_approvals');
    }
};
