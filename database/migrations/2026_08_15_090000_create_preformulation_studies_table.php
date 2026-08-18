<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preformulation_studies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('npd_proposal_id')->nullable()->constrained('npd_proposals')->cascadeOnDelete();
            $table->string('product_name');
            $table->text('product_concept')->nullable();
            $table->string('project_owner')->nullable();
            $table->enum('study_type', ['QBD Analysis', 'Study Preform']);
            $table->enum('status', ['Draft', 'In Progress', 'Completed', 'On Hold'])->default('Draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'])->default('Draft');
            $table->text('rejection_notes')->nullable();
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('preformulation_study_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preformulation_study_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preformulation_study_documents');
        Schema::dropIfExists('preformulation_studies');
    }
};