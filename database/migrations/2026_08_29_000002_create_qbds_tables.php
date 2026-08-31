<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── QbD ──
        Schema::create('qbds', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Attachments (pdf/word/img) ──
        Schema::create('qbd_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qbd_id')->constrained('qbds')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qbd_attachments');
        Schema::dropIfExists('qbds');
    }
};
