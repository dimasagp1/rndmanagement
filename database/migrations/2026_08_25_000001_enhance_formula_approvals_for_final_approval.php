<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Extend formula_approval_forms for Final Approval (Formula + Artwork/Design) ──
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            // Drop unique + FK on product_id first (MySQL uses unique index for FK)
            try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}
            try {
                $table->dropUnique('formula_approval_forms_product_id_unique');
            } catch (\Throwable $e) {
                try { $table->dropUnique(['product_id']); } catch (\Throwable $e2) {}
            }
            // Re-add FK as non-unique (will be indexed separately below)
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            // Formula linkage (Approved Formula as source)
            $table->foreignId('formula_id')->nullable()->after('product_id')->constrained('formulas')->nullOnDelete();

            // Revision number (Rev 00, 01, ...)
            $table->unsignedInteger('revision')->default(0)->after('approval_status');

            // Creator tracking (Staff initiator)
            $table->foreignId('created_by')->nullable()->after('revision')->constrained('users')->nullOnDelete();

            // ── Artwork / Design Approval ──
            $table->string('artwork_no')->nullable()->after('created_by');
            $table->string('artwork_title')->nullable()->after('artwork_no');
            $table->string('artwork_version')->nullable()->after('artwork_title');
            $table->text('artwork_description')->nullable()->after('artwork_version');
            $table->enum('artwork_status', ['Draft', 'Pending OM', 'Pending GM', 'Approved', 'Rejected'])->default('Draft')->after('artwork_description');
            $table->string('artwork_file_path')->nullable()->after('artwork_status');
            $table->string('artwork_original_name')->nullable()->after('artwork_file_path');
            $table->timestamp('artwork_uploaded_at')->nullable()->after('artwork_original_name');

            // ── Final Approved Document (generated after GM approval) ──
            $table->string('final_document_path')->nullable()->after('artwork_uploaded_at');
            $table->string('final_document_name')->nullable()->after('final_document_path');
            $table->timestamp('final_approved_at')->nullable()->after('final_document_name');
        });

        // Make product_id non-unique index for filtering
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('formula_id');
            $table->index('approval_status');
        });

        // ── Revision History ──
        Schema::create('formula_approval_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_approval_id')->constrained('formula_approval_forms')->cascadeOnDelete();
            $table->unsignedInteger('revision');
            $table->string('revision_label');
            $table->text('change_description')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Superseded', 'Approved'])->default('Approved');
            $table->timestamps();
        });

        // ── Approval Matrix snapshots (OM -> GM) ──
        Schema::create('formula_approval_approval_matrix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_approval_id')->constrained('formula_approval_forms')->cascadeOnDelete();
            $table->enum('step', ['Formula - OM Approval', 'Formula - GM Approval', 'Artwork - OM Approval', 'Artwork - GM Approval']);
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['formula_approval_id', 'step'], 'fa_matrix_unique');
        });

        // Add revision / is_final to attachments for traceability
        Schema::table('formula_approval_attachments', function (Blueprint $table) {
            $table->string('revision_label')->nullable()->after('original_name');
            $table->boolean('is_final_document')->default(false)->after('revision_label');
            $table->string('document_type')->default('Supporting')->after('is_final_document'); // Supporting | Artwork | Final
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_approval_approval_matrix');
        Schema::dropIfExists('formula_approval_revisions');

        Schema::table('formula_approval_attachments', function (Blueprint $table) {
            $table->dropColumn(['revision_label', 'is_final_document', 'document_type']);
        });

        Schema::table('formula_approval_forms', function (Blueprint $table) {
            try { $table->dropIndex(['product_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['formula_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['approval_status']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['formula_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
            $table->dropColumn([
                'formula_id',
                'revision',
                'created_by',
                'artwork_no',
                'artwork_title',
                'artwork_version',
                'artwork_description',
                'artwork_status',
                'artwork_file_path',
                'artwork_original_name',
                'artwork_uploaded_at',
                'final_document_path',
                'final_document_name',
                'final_approved_at',
            ]);
        });

        // Restore unique product_id (original state)
        Schema::table('formula_approval_forms', function (Blueprint $table) {
            $table->unique('product_id');
        });
    }
};
