<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npd_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('prf_id')->constrained('prfs')->onDelete('cascade');
            $table->string('product_name');
            $table->text('product_concept');
            $table->decimal('target_cogs', 14, 2);
            $table->decimal('target_selling_price', 14, 2);
            $table->date('development_start')->nullable();
            $table->date('development_end')->nullable();
            $table->string('pic')->nullable();
            $table->text('project_team')->nullable();
            $table->enum('approval_status', ['Draft', 'Pending Tahap 1', 'Approval by OM', 'Completed by GM', 'Rejected'])->default('Draft');
            $table->enum('project_status', ['Draft', 'On Track', 'In Progress', 'On Hold', 'Delayed', 'Completed'])->default('Draft');
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('npd_proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npd_proposal_id')->constrained('npd_proposals')->onDelete('cascade');
            $table->string('document_type')->default('Lainnya');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npd_proposal_documents');
        Schema::dropIfExists('npd_proposals');
    }
};