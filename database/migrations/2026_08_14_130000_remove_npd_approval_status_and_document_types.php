<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NPD Proposal tidak lagi memiliki status approval (tanpa OM/GM)
        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        // Tipe dokumen tidak lagi diperlukan (dropdown dihapus dari form upload)
        Schema::table('prf_documents', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });

        Schema::table('npd_proposal_documents', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }

    public function down(): void
    {
        Schema::table('npd_proposals', function (Blueprint $table) {
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Approval by OM', 'Completed by GM', 'Rejected'])->default('Draft')->after('project_team');
        });

        Schema::table('prf_documents', function (Blueprint $table) {
            $table->string('document_type')->default('Lainnya')->after('prf_id');
        });

        Schema::table('npd_proposal_documents', function (Blueprint $table) {
            $table->string('document_type')->default('Lainnya')->after('npd_proposal_id');
        });
    }
};