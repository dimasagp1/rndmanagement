<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── NIE Approved ──
        Schema::create('nie_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Attachments (pdf/word/img) ──
        Schema::create('nie_approval_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nie_approval_id')->constrained('nie_approvals')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nie_approval_attachments');
        Schema::dropIfExists('nie_approvals');
    }
};