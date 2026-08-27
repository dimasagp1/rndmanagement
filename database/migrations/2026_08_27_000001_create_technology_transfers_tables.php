<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Technology Transfer ──
        Schema::create('technology_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Attachments (pdf/word/img) ──
        Schema::create('technology_transfer_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technology_transfer_id')->constrained('technology_transfers')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technology_transfer_attachments');
        Schema::dropIfExists('technology_transfers');
    }
};
