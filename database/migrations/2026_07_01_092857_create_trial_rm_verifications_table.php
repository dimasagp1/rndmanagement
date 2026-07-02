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
        Schema::create('trial_rm_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_rm_id')->constrained('trial_rms')->onDelete('cascade');
            $table->string('parameter_name'); // Warna, pH, Viskositas, dll
            $table->string('target_value');
            $table->string('actual_value');
            $table->enum('status', ['Pass', 'Fail', 'Warning'])->default('Pass');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_rm_verifications');
    }
};
