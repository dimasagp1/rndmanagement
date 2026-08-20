<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_developments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->string('product_category');
            $table->string('packaging_type');
            $table->string('development_purpose');
            $table->date('target_launch');
            $table->string('target_market')->nullable();
            $table->enum('approval_status', ['Draft', 'Pending OM', 'Pending GM', 'Approved', 'Rejected'])->default('Draft');
            $table->enum('development_stage', ['Draft', 'In Development', 'Material Evaluation', 'Packaging Trial', 'Compatibility Evaluation', 'In Review', 'Approved', 'Rejected', 'Cancelled', 'Obsolete'])->default('Draft');
            $table->unsignedInteger('revision')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_om')->nullable();
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_gm')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('packaging_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('specification_no')->nullable();
            $table->string('packaging_type');
            $table->string('dimension')->nullable();
            $table->string('nominal_weight')->nullable();
            $table->string('tolerance')->nullable();
            $table->string('material_structure')->nullable();
            $table->string('thickness')->nullable();
            $table->string('color')->nullable();
            $table->string('printing')->nullable();
            $table->string('sealing_type')->nullable();
            $table->string('shelf_life')->nullable();
            $table->string('storage_condition')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_primary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('packaging_type');
            $table->string('material')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('dimension')->nullable();
            $table->string('thickness')->nullable();
            $table->enum('product_contact', ['Yes', 'No'])->default('No');
            $table->string('barrier_requirement')->nullable();
            $table->enum('light_protection', ['Yes', 'No'])->default('No');
            $table->enum('moisture_protection', ['Yes', 'No'])->default('No');
            $table->enum('oxygen_protection', ['Yes', 'No'])->default('No');
            $table->string('seal_requirement')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_secondary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('packaging_type');
            $table->string('material')->nullable();
            $table->string('dimension')->nullable();
            $table->string('printing')->nullable();
            $table->string('finishing')->nullable();
            $table->string('quantity_per_box')->nullable();
            $table->string('supplier_name')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_material_developments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->string('material_name');
            $table->string('material_type')->nullable();
            $table->string('current_material')->nullable();
            $table->string('proposed_material');
            $table->string('material_specification')->nullable();
            $table->text('reason_for_change');
            $table->text('expected_benefit');
            $table->enum('risk', ['Low', 'Medium', 'High'])->default('Low');
            $table->enum('status', ['Under Evaluation', 'Approved', 'Rejected'])->default('Under Evaluation');
            $table->timestamps();
        });

        Schema::create('packaging_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_name');
            $table->string('supplier_code')->nullable();
            $table->string('material')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('qualification_status', ['New', 'Under Qualification', 'Qualified', 'Conditional', 'Rejected', 'Inactive'])->default('New');
            $table->enum('supplier_status', ['Active', 'Inactive'])->default('Active');
            $table->string('certificate')->nullable();
            $table->enum('audit_status', ['Pending', 'Passed', 'Failed'])->default('Pending');
            $table->date('approval_date')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->date('trial_date');
            $table->string('trial_batch')->nullable();
            $table->string('packaging_material');
            $table->string('machine')->nullable();
            $table->string('quantity')->nullable();
            $table->string('operator')->nullable();
            $table->string('trial_purpose')->nullable();
            $table->enum('result', ['Pass', 'Conditional Pass', 'Fail']);
            $table->text('failure_reason')->nullable();
            $table->text('corrective_action')->nullable();
            $table->enum('retest_required', ['Yes', 'No'])->default('No');
            $table->foreignId('retest_of')->nullable()->constrained('packaging_trials')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('packaging_trial_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_trial_id')->constrained()->cascadeOnDelete();
            $table->string('parameter');
            $table->string('target')->nullable();
            $table->string('actual')->nullable();
            $table->enum('result', ['Pass', 'Fail'])->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_compatibility_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->index('pkg_compat_dev_idx');
            $table->foreign('packaging_development_id', 'pkg_compat_dev_fk')->references('id')->on('packaging_developments')->cascadeOnDelete();
            $table->date('evaluation_date');
            $table->string('evaluation_method');
            $table->string('test_condition')->nullable();
            $table->string('test_duration')->nullable();
            $table->string('evaluator')->nullable();
            $table->enum('result', ['Pass', 'Fail', 'Conditional']);
            $table->string('conclusion')->nullable();
            $table->text('finding')->nullable();
            $table->string('risk')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_compatibility_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_compatibility_id')->index('pkg_compat_param_idx');
            $table->foreign('packaging_compatibility_id', 'pkg_compat_param_fk')->references('id')->on('packaging_compatibility_evaluations')->cascadeOnDelete();
            $table->string('parameter');
            $table->enum('result', ['Pass', 'Fail']);
            $table->timestamps();
        });

        Schema::create('packaging_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->string('document_no')->nullable();
            $table->string('document_name');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('revision');
            $table->enum('status', ['Draft', 'Approved', 'Rejected'])->default('Draft');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('packaging_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->enum('step', ['OM Approval', 'GM Approval']);
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->string('revision');
            $table->text('change_description')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Superseded', 'Approved'])->default('Approved');
            $table->timestamps();
        });

        Schema::create('packaging_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_development_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_audit_logs');
        Schema::dropIfExists('packaging_revisions');
        Schema::dropIfExists('packaging_approvals');
        Schema::dropIfExists('packaging_attachments');
        Schema::dropIfExists('packaging_compatibility_parameters');
        Schema::dropIfExists('packaging_compatibility_evaluations');
        Schema::dropIfExists('packaging_trial_parameters');
        Schema::dropIfExists('packaging_trials');
        Schema::dropIfExists('packaging_suppliers');
        Schema::dropIfExists('packaging_material_developments');
        Schema::dropIfExists('packaging_secondary');
        Schema::dropIfExists('packaging_primary');
        Schema::dropIfExists('packaging_specifications');
        Schema::dropIfExists('packaging_developments');
    }
};