<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Folders (hierarchical) ──
        Schema::create('commercial_production_folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('parent_id');
        });

        // ── Documents (files) ──
        Schema::create('commercial_production_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('commercial_production_folders')->nullOnDelete();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('extension')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['folder_id', 'original_name']);
        });

        // ── Document Versions (history) ──
        Schema::create('commercial_production_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_production_document_id')->constrained('commercial_production_documents')->name('cpdv_doc_id_foreign')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Audit Trail ──
        Schema::create('commercial_production_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_production_audits');
        Schema::dropIfExists('commercial_production_document_versions');
        Schema::dropIfExists('commercial_production_documents');
        Schema::dropIfExists('commercial_production_folders');
    }
};